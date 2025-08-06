#!/usr/bin/env python3
import os
import re
import sys
from typing import Tuple, List, Dict

def renamePrefixToA(folderPath: str) -> Tuple[str, int]:
    try:
        all_files = os.listdir(folderPath)
    except FileNotFoundError:
        print(f"❌ 错误: 文件夹 '{folderPath}' 不存在。")
        return 0

    # 1. 使用集合操作快速找到成对文件的基本名
    in_files = {f[:-3] for f in all_files if f.endswith('.in')}
    ans_files = {f[:-4] for f in all_files if f.endswith('.ans')}
    
    # 2. 获取交集并排序
    paired_basenames = sorted(list(in_files.intersection(ans_files)))

    if not paired_basenames:
        print("⚠️ 未找到匹配的文件对。")
        return 0
    
    # 3. 遍历排序后的列表并重命名
    for i, basename in enumerate(paired_basenames, 1):
        new_in_path = os.path.join(folderPath, f"a{i}.in")
        new_ans_path = os.path.join(folderPath, f"a{i}.ans")

        # 安全检查，防止覆盖现有文件
        if not (os.path.exists(new_in_path) or os.path.exists(new_ans_path)):
            os.rename(os.path.join(folderPath, f"{basename}.in"), new_in_path)
            os.rename(os.path.join(folderPath, f"{basename}.ans"), new_ans_path)
            print(f"{basename}.in/.ans → a{i}.in/.ans", file=sys.stderr, flush=True)

    return len(paired_basenames)

def generate_problem_conf(folder_path: str, n_tests: int):
    content = f"""n_tests {n_tests}
n_subtasks 1
subtask_end_1 {n_tests}
n_ex_tests 0
n_sample_tests 0
input_pre a
input_suf in
output_pre a
output_suf ans
time_limit 2
memory_limit 512
output_limit 64
checker_time_limit 2
use_builtin_judger on
"""
    conf_path = os.path.join(folder_path, "problem.conf")
    with open(conf_path, "w") as f:
        f.write(content)
    print(f"✅ 已生成 {conf_path}", file=sys.stderr, flush=True)


if __name__ == "__main__":
    if len(sys.argv) != 2:
        print(f"用法: {sys.argv[0]} <目标目录>", file=sys.stderr, flush=True)
        sys.exit(1)

    maxK = renamePrefixToA(sys.argv[1])
    print(f"最大 k: {maxK}", file=sys.stderr, flush=True)
    generate_problem_conf(sys.argv[1], maxK)
