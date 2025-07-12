#!/usr/bin/env python3
import os
import re
import sys
from typing import Tuple

def renamePrefixToA(folderPath: str) -> Tuple[str, int]:
    """
    将 <prefix><k>.ans 重命名为 a<k>.ans。
    返回 (prefix, maxK)；若未匹配到文件，则返回 ("", -1)。
    """
    pattern_ans = re.compile(r'^([^\d]*)(\d+)\.ans$', re.IGNORECASE)
    pattern_input = re.compile(r'^([^\d]*)(\d+)\.in$', re.IGNORECASE)
    foundPrefix, maxK = "", -1

    for fileName in os.listdir(folderPath):
        if (m := pattern_ans.match(fileName)):
            prefix, kStr = m.groups()
            k = int(kStr)
            foundPrefix, maxK = prefix, max(maxK, k)

            oldPath = os.path.join(folderPath, fileName)
            newPath = os.path.join(folderPath, f"a{k}.ans")
            if os.path.exists(newPath):
                raise FileExistsError(f"{newPath} 已存在，避免覆盖")
            os.rename(oldPath, newPath)

            print(f"{fileName} → a{k}.ans")

        elif (m := pattern_input.match(fileName)):
            prefix, kStr = m.groups()
            k = int(kStr)
            foundPrefix, maxK = prefix, max(maxK, k)

            oldPath = os.path.join(folderPath, fileName)
            newPath = os.path.join(folderPath, f"a{k}.in")
            if os.path.exists(newPath):
                raise FileExistsError(f"{newPath} 已存在，避免覆盖")
            os.rename(oldPath, newPath)

            print(f"{fileName} → a{k}.in")

    if maxK == -1:
        print("⚠️  未找到匹配文件")
    return foundPrefix, maxK

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
time_limit 1
memory_limit 512
output_limit 64
use_builtin_judger on
"""
    conf_path = os.path.join(folder_path, "problem.conf")
    with open(conf_path, "w") as f:
        f.write(content)
    print(f"✅ 已生成 {conf_path}")


if __name__ == "__main__":
    if len(sys.argv) != 2:
        print(f"用法: {sys.argv[0]} <目标目录>")
        sys.exit(1)

    prefix, maxK = renamePrefixToA(sys.argv[1])
    print(f"原前缀: “{prefix}”  最大 k: {maxK}")
