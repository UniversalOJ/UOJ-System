import json
import pymysql
import zipfile
import os, re
import random
import string
import sys
from pathlib import Path
import argparse

from database_interface import UOJDatabaseInterface, sync_problem
from compile_checker import compile_checker
from data_preprocess import renamePrefixToA, generate_problem_conf

def setup_problems(base_folder, db_interface, uoj_problem_base):
    """
    配置题目：建立题号映射表，编译checker，生成problem.conf，同步到UOJ
    
    Args:
        base_folder: 包含所有题目文件夹的基础目录
        db_interface: UOJDatabaseInterface实例
        uoj_problem_base: UOJ题目存储路径，如 ~/UOJ-System/uoj_data/upload/problem/
    """
    base_path = Path(base_folder).expanduser().resolve()
    mapping_file = base_path / "problem_mapping.json"
    
    # 读取或创建题号映射表
    if mapping_file.exists():
        with open(mapping_file, 'r', encoding='utf-8') as f:
            problem_mapping = json.load(f)
    else:
        problem_mapping = {}
    
    # 扫描所有题目文件夹
    problem_folders = [d for d in base_path.iterdir() if d.is_dir() and not d.name.startswith('.')]
    
    print(f"找到 {len(problem_folders)} 个题目文件夹")
    
    for folder in sorted(problem_folders):
        folder_name = folder.name
        db_interface.reconnect()  # 确保数据库连接是最新的
        
        # 检查是否已有映射
        if folder_name not in problem_mapping:
            # 创建新题目
            problem_id = db_interface.new_problem(title=folder_name)
            if problem_id:
                problem_mapping[folder_name] = problem_id
                print(f"✅ 为文件夹 '{folder_name}' 创建新题目，ID: {problem_id}")
            else:
                print(f"❌ 无法为文件夹 '{folder_name}' 创建题目")
                continue
        else:
            problem_id = problem_mapping[folder_name]
            # 验证题目ID是否存在
            if not db_interface.id_verify(problem_id):
                print(f"⚠️  题目ID {problem_id} 不存在，重新创建")
                problem_id = db_interface.new_problem(title=folder_name)
                if problem_id:
                    problem_mapping[folder_name] = problem_id
                else:
                    print(f"❌ 无法为文件夹 '{folder_name}' 创建题目")
                    continue
            
            print(f"✅ 题目已经配置完毕: {problem_id}")
            continue
        
        print(f"\n处理题目: {folder_name} (ID: {problem_id})")
        
        # 1. 编译checker
        try:
            compile_checker(str(folder))
            print("✓ Checker编译成功")
        except Exception as e:
            print(f"⚠️  Checker编译失败: {e}")
        
        # 2. 重命名测试数据并生成problem.conf
        try:
            max_k = renamePrefixToA(str(folder))
            if max_k > 0:
                generate_problem_conf(str(folder), max_k)
                print(f"✓ 生成problem.conf，共 {max_k} 组数据")
            else:
                print("⚠️  未找到测试数据")
        except Exception as e:
            print(f"❌ 处理测试数据失败: {e}")
            continue
        
        # 3. 同步到UOJ
        try:
            sync_problem(str(folder), str(problem_id), uoj_problem_base)
            print("✓ 同步到UOJ成功")
        except Exception as e:
            print(f"❌ 同步失败: {e}")
    
    # 保存映射表
    with open(mapping_file, 'w', encoding='utf-8') as f:
        json.dump(problem_mapping, f, indent=2, ensure_ascii=False)
    
    print(f"\n✅ 题目配置完成，映射表已保存至: {mapping_file}")
    return problem_mapping

def code_process(code):
    lang = 'C++17' if '#include' in code else 'Python3'
    if lang == 'C++17':
        code = code.replace('std::endl', "'\\n'")
        code = code.replace('endl', "'\\n'")
    return code, lang

def submit_all_solutions(base_folder, submission_base_folder, db_interface, storage_path):
    """
    提交所有题目的所有解答
    
    Args:
        base_folder: 包含problem_mapping.json的基础目录（题目配置目录）
        submission_base_folder: 独立的提交文件夹，包含与题目同名的子文件夹
        db_interface: UOJDatabaseInterface实例
        storage_path: UOJ提交存储路径
    
    Returns:
        submission_mapping: 提交映射表 {文件路径: submission_id}
    """
    base_path = Path(base_folder).expanduser().resolve()
    submission_base_path = Path(submission_base_folder).expanduser().resolve()
    
    mapping_file = base_path / "problem_mapping.json"
    submission_mapping_file = submission_base_path / "submission_mapping.json"
    
    # 读取题号映射表
    if not mapping_file.exists():
        print("❌ 题号映射表不存在，请先运行 setup_problems")
        return {}
    
    with open(mapping_file, 'r', encoding='utf-8') as f:
        problem_mapping = json.load(f)
    
    # 读取或创建提交映射表
    if submission_mapping_file.exists():
        with open(submission_mapping_file, 'r', encoding='utf-8') as f:
            submission_mapping = json.load(f)
    else:
        submission_mapping = {}
    
    total_submissions = 0
    
    # 遍历题号映射表中的每个题目
    for folder_name, problem_id in problem_mapping.items():
        # 在提交文件夹中查找对应的题目文件夹
        submission_folder = submission_base_path / folder_name
        
        if not submission_folder.exists():
            print(f"⚠️  提交文件夹中没有题目 {folder_name} 的文件夹")
            continue
        
        # 查找所有cpp文件
        cpp_files = list(submission_folder.glob("*.cpp"))
        if not cpp_files:
            print(f"⚠️  题目 {folder_name} 文件夹中没有cpp文件")
            continue
        
        print(f"\n处理题目 {folder_name} (ID: {problem_id}) 的 {len(cpp_files)} 个提交")
        
        for cpp_file in cpp_files:
            # 使用相对于submission_base_path的路径作为key
            file_key = str(cpp_file.relative_to(submission_base_path))
            
            # 检查是否已经提交过
            if file_key in submission_mapping:
                print(f"  ✓ {cpp_file.name} 已提交，ID: {submission_mapping[file_key]}")
                continue
            
            # 读取代码
            try:
                with open(cpp_file, 'r', encoding='utf-8') as f:
                    code = f.read()
            except Exception as e:
                print(f"  ❌ 无法读取 {cpp_file.name}: {e}")
                continue
            
            _code, _lang = code_process(code)
            # 提交代码
            submission_id = db_interface.submit_code(
                problem_id=problem_id,
                username='admin',
                code=_code,
                language=_lang,
                storage_path=storage_path
            )
            
            if submission_id:
                submission_mapping[file_key] = submission_id
                total_submissions += 1
                print(f"  ✓ {cpp_file.name} 提交成功，ID: {submission_id}")
            else:
                print(f"  ❌ {cpp_file.name} 提交失败")
    
    # 保存提交映射表到提交文件夹中
    with open(submission_mapping_file, 'w', encoding='utf-8') as f:
        json.dump(submission_mapping, f, indent=2, ensure_ascii=False, sort_keys=True)
    
    print(f"\n✅ 提交完成，共提交 {total_submissions} 份代码")
    print(f"提交映射表已保存至: {submission_mapping_file}")
    return submission_mapping


def fetch_all_results(submission_base_folder, db_interface):
    """
    获取所有提交的评测结果
    
    Args:
        submission_base_folder: 包含submission_mapping.json的提交文件夹
        db_interface: UOJDatabaseInterface实例
    
    Returns:
        results: 评测结果字典
    """
    submission_base_path = Path(submission_base_folder).expanduser().resolve()
    submission_mapping_file = submission_base_path / "submission_mapping.json"
    results_file = submission_base_path / "submission_results.json"
    short_results_file = submission_base_path / "submission_short_results.json"
    
    # 读取提交映射表
    print(submission_mapping_file)
    if not submission_mapping_file.exists():
        print("❌ 提交映射表不存在，请先运行 submit_all_solutions")
        return {}
    
    with open(submission_mapping_file, 'r', encoding='utf-8') as f:
        submission_mapping = json.load(f)
    
    if not submission_mapping:
        print("⚠️  没有找到任何提交记录")
        return {}
    
    print(f"开始获取 {len(submission_mapping)} 个提交的评测结果...")
    
    results = {}
    
    # 构建查询
    submission_ids = list(submission_mapping.values())
    
    for ids in submission_ids:
        #db_interface.reconnect()  # 确保数据库连接是最新的
        js = db_interface.fetch_result(ids)
        results[ids] = js
        
    #json.dump(results, open(results_file, 'w', encoding='utf-8'), indent=2, ensure_ascii=False)
    formal_results = {}
    short_results = {}
    for key in submission_mapping.keys():
        if submission_mapping[key] in results:
            formal_results[key] = results[submission_mapping[key]]
            res = results[submission_mapping[key]]
            details = res.get("details", "")
            matches = re.findall(r'info="([^"]+)"', details)
            info = matches[-1] if matches else "N/A"
            res_s = {"score": res.get("score", 0), "info": info, "submission_id": submission_mapping[key]}
            if "error" in res:
                res_s["info"] = res["error"]
            short_results[key] = res_s
        else:
            print(f"⚠️  提交 {key} 的结果未找到")

    json.dump(formal_results, open(results_file, 'w', encoding='utf-8'), indent=2, ensure_ascii=False)
    json.dump(short_results, open(short_results_file, 'w', encoding='utf-8'), indent=2, ensure_ascii=False, sort_keys=True)
    print(f"\n✅ 结果已保存至: {results_file}")
    return results


# 主程序示例
if __name__ == "__main__":
    # 数据库配置
    db_config = {
        'host': 'localhost',
        'port': 3306,
        'user': 'root',
        'password': 'root',
        'database': 'app_uoj233',
    }
    
    # 路径配置
    base_folder = "~/judge2/problems/"  # 包含所有题目文件夹的目录
    submission_folder = "~/judge2/submissions/"   # 每个题目文件夹内的提交文件夹名
    uoj_problem_base = "~/UOJ-System/uoj_data/web/data"
    storage_path = "~/UOJ-System/uoj_data/web/storage/"

    parser = argparse.ArgumentParser(description="UOJ Judger Controller")
    parser.add_argument("command", choices=["setup", "submit", "fetch"], help="要执行的命令")
    parser.add_argument("--base", default=base_folder, help="题目文件夹目录")
    parser.add_argument("--submission", default=submission_folder, help="提交文件夹目录")

    args = parser.parse_args()
    base_folder = Path(args.base).expanduser().resolve()
    submission_folder = Path(args.submission).expanduser().resolve()

    # 创建数据库接口
    db = UOJDatabaseInterface(db_config, storage_path)

    # 1. 配置题目
    # python main.py setup
    if args.command == "setup":
        setup_problems(base_folder, db, uoj_problem_base)
    
    # 2. 提交评测
    # python main.py submit
    elif args.command == "submit":
        submit_all_solutions(base_folder, submission_folder, db, storage_path)
    
    # 3. 获取结果
    # python main.py fetch
    elif args.command == "fetch":
        fetch_all_results(submission_folder, db)
    
    else:
        print("用法:")
        print("  python main.py setup   - 配置题目")
        print("  python main.py submit  - 提交评测")
        print("  python main.py fetch   - 获取结果")
    
    db.close()