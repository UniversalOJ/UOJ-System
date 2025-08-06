#!/usr/bin/env python3
import os, shutil, subprocess, sys
from typing import List, Dict

def run(cmd: List[str], **kw):
    """打印并执行一条命令"""
    print("▶", " ".join(cmd), file=sys.stderr, flush=True)
    return subprocess.check_call(cmd, **kw)

def compile_checker(src_dir: str) -> None:
    """
    在本地（容器内）编译 C++ 检查器 (checker)。

    :param src_dir: 包含 chk.cpp 或 checker.cpp 的目录。
    """
    chk_cpp = os.path.join(src_dir, "chk.cpp")
    checker_cpp = os.path.join(src_dir, "checker.cpp")

    # 如果 chk.cpp 不存在，则尝试从 checker.cpp 复制
    if not os.path.isfile(chk_cpp):
        if os.path.isfile(checker_cpp):
            shutil.copy(checker_cpp, chk_cpp)
            print(f"✅ 已将 checker.cpp 复制为 chk.cpp", file=sys.stderr, flush=True)
        else:
            sys.exit(f"❌ 在目录 {src_dir} 中找不到 chk.cpp 或 checker.cpp")

    # 定义输出文件路径
    output_exe = os.path.join(src_dir, "chk")
    
    # 定义 UOJ 头文件路径 (在标准 UOJ 容器内)
    include_dir = "/opt/uoj/judger/uoj_judger/include"

    # 构建 g++ 编译命令
    compile_cmd = [
        "g++",
        "-o", output_exe,      # 输出文件
        chk_cpp,               # 输入源文件
        "-O2",
        "--std=c++17",
        "-lm",
        "-w",                  # 抑制警告
        "-DONLINE_JUDGE",
        f"-I{include_dir}",    # UOJ 框架头文件
        f"-I{src_dir}",        # 允许 #include "some_local_header.h"
    ]

    # 执行编译
    try:
        run(compile_cmd)
        # 为生成的可执行文件添加执行权限
        os.chmod(output_exe, 0o755)
        print(f"✅ 编译成功 → {output_exe}", file=sys.stderr, flush=True)
    except subprocess.CalledProcessError as e:
        sys.exit(f"❌ 编译失败，返回码: {e.returncode}")
    except FileNotFoundError:
        sys.exit("❌ 编译失败，找不到 g++。请确保您在正确的容器环境中，并且已安装 g++。")


if __name__ == "__main__":
    if len(sys.argv) != 2:
        sys.exit("用法：python3 compile_checker_local.py /path/to/checker_folder")
    
    checker_folder = sys.argv[1]
    if not os.path.isdir(checker_folder):
        sys.exit(f"❌ 错误：提供的路径不是一个有效的目录 -> {checker_folder}")
        
    compile_checker(checker_folder)