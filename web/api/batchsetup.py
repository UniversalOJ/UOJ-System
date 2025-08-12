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
    
    print(f"找到 {len(problem_folders)} 个题目文件夹", file=sys.stderr, flush=True)
    
    for folder in sorted(problem_folders):
        folder_name = folder.name
        db_interface.reconnect()  # 确保数据库连接是最新的
        
        # 检查是否已有映射
        if folder_name not in problem_mapping:
            # 创建新题目
            problem_id = db_interface.new_problem(title=folder_name)
            if problem_id:
                problem_mapping[folder_name] = problem_id
                print(f"✅ 为文件夹 '{folder_name}' 创建新题目，ID: {problem_id}", file=sys.stderr, flush=True)
            else:
                print(f"❌ 无法为文件夹 '{folder_name}' 创建题目")
                continue
        else:
            problem_id = problem_mapping[folder_name]
            # 验证题目ID是否存在
            if not db_interface.id_verify(problem_id):
                print(f"⚠️  题目ID {problem_id} 不存在，重新创建", file=sys.stderr, flush=True)
                problem_id = db_interface.new_problem(title=folder_name)
                if problem_id:
                    problem_mapping[folder_name] = problem_id
                else:
                    print(f"❌ 无法为文件夹 '{folder_name}' 创建题目")
                    continue
            
            print(f"✅ 题目已经配置完毕: {problem_id}", file=sys.stderr, flush=True)
            continue
        
        print(f"\n处理题目: {folder_name} (ID: {problem_id})", file=sys.stderr, flush=True)
        
        # 1. 编译checker
        try:
            compile_checker(str(folder))
            print("✓ Checker编译成功", file=sys.stderr, flush=True)
        except Exception as e:
            print(f"⚠️  Checker编译失败: {e}")
        
        # 2. 重命名测试数据并生成problem.conf
        try:
            max_k = renamePrefixToA(str(folder))
            if max_k > 0:
                generate_problem_conf(str(folder), max_k)
                print(f"✓ 生成problem.conf，共 {max_k} 组数据", file=sys.stderr, flush=True)
            else:
                print("⚠️  未找到测试数据", file=sys.stderr, flush=True)
        except Exception as e:
            print(f"❌ 处理测试数据失败: {e}")
            continue
        
        # 3. 同步到UOJ
        try:
            sync_problem(str(folder), str(problem_id), uoj_problem_base)
            print("✓ 同步到UOJ成功", file=sys.stderr, flush=True)
        except Exception as e:
            print(f"❌ 同步失败: {e}")
    
    # 保存映射表
    with open(mapping_file, 'w', encoding='utf-8') as f:
        json.dump(problem_mapping, f, indent=2, ensure_ascii=False)
    
    print(f"\n✅ 题目配置完成，映射表已保存至: {mapping_file}", file=sys.stderr, flush=True)
    return problem_mapping

def batchsetup(base_folder, db_interface, uoj_problem_base):
    base_path = Path(base_folder).expanduser().resolve()
    mapping_file = base_path / "problem_mapping.json"
    problem_mapping = {}
    problem_folders = [d for d in base_path.iterdir() if d.is_dir() and not d.name.startswith('.')]
    print(f"找到 {len(problem_folders)} 个题目文件夹", file=sys.stderr, flush=True)
    #print(f"找到 {len(problem_folders)} 个题目文件夹")

    for folder in sorted(problem_folders):
        folder_name = folder.name
        db_interface.reconnect()  # 确保数据库连接是最新的
        
        
        problem_id = db_interface.new_problem(title=folder_name)
        if problem_id:
            problem_mapping[folder_name] = problem_id
            print(f"✅ 为文件夹 '{folder_name}' 创建新题目，ID: {problem_id}", file=sys.stderr, flush=True)
        else:
            print(f"❌ 无法为文件夹 '{folder_name}' 创建题目", file=sys.stderr, flush=True)
            continue
        
        print(f"\n处理题目: {folder_name} (ID: {problem_id})", file=sys.stderr, flush=True)
        
        # 1. 编译checker
        try:
            compile_checker(str(folder))
            print("✓ Checker编译成功", file=sys.stderr, flush=True)
        except Exception as e:
            print(f"⚠️  Checker编译失败: {e}", file=sys.stderr, flush=True)

        # 2. 重命名测试数据并生成problem.conf
        try:
            max_k = renamePrefixToA(str(folder))
            if max_k > 0:
                generate_problem_conf(str(folder), max_k)
                print(f"✓ 生成problem.conf，共 {max_k} 组数据", file=sys.stderr, flush=True)
            else:
                print("⚠️  未找到测试数据", file=sys.stderr, flush=True)
        except Exception as e:
            print(f"❌ 处理测试数据失败: {e}", file=sys.stderr, flush=True)
            continue
        
        # 3. 同步到UOJ
        try:
            sync_problem(str(folder), str(problem_id), uoj_problem_base)
            print("✓ 同步到UOJ成功", file=sys.stderr, flush=True)
        except Exception as e:
            print(f"❌ 同步失败: {e}", file=sys.stderr, flush=True)

    print(json.dumps(problem_mapping, indent=2, ensure_ascii=False), file=sys.stdout, flush=True)
    return problem_mapping

def main():
    parser = argparse.ArgumentParser(description="批量配置UOJ题目")
    parser.add_argument("base_folder", type=str, help="包含所有题目文件夹的基础目录")
    parser.add_argument("--db_host", type=str, default="uoj-db", help="数据库主机")
    parser.add_argument("--db_user", type=str, default="root", help="数据库用户名")
    parser.add_argument("--db_password", type=str, default="root", help="数据库密码")
    parser.add_argument("--db_name", type=str, default="app_uoj233", help="数据库名称")
    parser.add_argument("--uoj_problem_base", type=str, default="/var/uoj_data/", help="UOJ题目存储路径")
    parser.add_argument("--storage_path", type=str, default="/opt/uoj/web/app/storage/", help="存储路径")

    args = parser.parse_args()

    db_config = {
        'host': args.db_host,
        'port': 3306,
        'user': args.db_user,
        'password': args.db_password,
        'database': 'app_uoj233',
    }
    
    # 初始化数据库接口
    db_interface = UOJDatabaseInterface(db_config, args.storage_path)
    
    # 执行题目配置
    batchsetup(args.base_folder, db_interface, args.uoj_problem_base)

if __name__ == "__main__":
    main()