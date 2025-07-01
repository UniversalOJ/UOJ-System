#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import os
import sys
import shutil
import zipfile
import argparse
import tempfile
from pathlib import Path

# --- 配置区 ---
TARGET_BASE_DIR = Path("./uoj_data/web/data")
# --- 配置区结束 ---

def create_zip_with_folder(source_dir, zip_path):
    """
    创建包含源文件夹本身的zip文件。
    """
    source_path = Path(source_dir)
    zip_path = Path(zip_path)
    
    with zipfile.ZipFile(zip_path, 'w', zipfile.ZIP_DEFLATED) as zipf:
        folder_name = source_path.name
        for file_path in source_path.rglob('*'):
            if file_path.is_file():
                arcname = Path(folder_name) / file_path.relative_to(source_path)
                zipf.write(file_path, arcname)

def main():
    parser = argparse.ArgumentParser(description='UOJ题目数据打包工具 (最终版)')
    parser.add_argument('source_folder', help='源文件夹路径')
    parser.add_argument('problem_id', help='题目ID')
    
    args = parser.parse_args()
    
    source_folder = Path(args.source_folder).resolve()
    problem_id = args.problem_id
    
    if not source_folder.is_dir():
        print(f"❌ 错误: 源文件夹 {source_folder} 不存在或不是一个目录。")
        sys.exit(1)
    
    if not problem_id.isdigit():
        print(f"❌ 错误: 题目ID '{problem_id}' 必须是数字。")
        sys.exit(1)

    target_folder = TARGET_BASE_DIR / problem_id
    target_zip = TARGET_BASE_DIR / f"{problem_id}.zip"

    try:
        print("--- 开始清理目标位置 ---")
        if target_folder.exists():
            shutil.rmtree(target_folder)
            print(f"🗑️ 已删除旧的目标文件夹: {target_folder}")
        
        if target_zip.exists():
            target_zip.unlink()
            print(f"🗑️ 已删除旧的目标ZIP: {target_zip}")

        with tempfile.TemporaryDirectory(prefix=f"uoj_{problem_id}_") as temp_dir_str:
            temp_dir = Path(temp_dir_str)
            print(f"\n--- 在安全的临时目录中操作: {temp_dir} ---")

            work_folder = temp_dir / problem_id
            
            # 【修改点 1】定义临时zip文件的路径
            temp_zip_path = temp_dir / f"{problem_id}.zip"
            
            print(f"📂 复制: {source_folder} -> {work_folder}")
            shutil.copytree(source_folder, work_folder)
            
            # 【修改点 2】在临时目录中创建zip文件
            print(f"📦 在临时目录中创建ZIP: {temp_zip_path}")
            create_zip_with_folder(work_folder, temp_zip_path)
            
            # 【修改点 3】将临时zip文件移动到最终位置
            print(f"🚚 移动ZIP文件: {temp_zip_path} -> {target_zip}")
            shutil.move(str(temp_zip_path), str(target_zip))

            # 移动文件夹（这部分逻辑不变）
            print(f"🚚 移动文件夹: {work_folder} -> {target_folder}")
            shutil.move(str(work_folder), str(target_folder))
        
        print("\n--- 操作完成，验证结果 ---")
        if not target_folder.exists() or not target_zip.exists():
             raise Exception("验证失败，目标文件或文件夹未成功创建！")

        print(f"\n✅ 成功!")
        print(f"  -> 文件夹位置: {target_folder}")
        print(f"  -> ZIP文件位置: {target_zip}")
        
        print(f"\n📋 ZIP文件内容预览:")
        with zipfile.ZipFile(target_zip, 'r') as zipf:
            file_list = zipf.namelist()
            for filename in file_list[:10]:
                print(f"     {filename}")
            if len(file_list) > 10:
                print(f"     ... 还有 {len(file_list) - 10} 个文件")
            
    except Exception as e:
        print(f"\n❌ 处理过程中发生严重错误: {e}")
        import traceback
        traceback.print_exc()
        sys.exit(1)

if __name__ == "__main__":
    main()