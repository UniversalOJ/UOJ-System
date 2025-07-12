#!/usr/bin/env python3
import os, shutil, subprocess, sys, uuid

def run(cmd: list[str], capture=False, **kw):
    print("▶", " ".join(cmd))
    return subprocess.check_output(cmd, text=True, **kw) if capture else subprocess.check_call(cmd, **kw)

def compile_checker(host_src: str, container: str = "uoj-judger") -> None:
    interactor_cpp = os.path.join(host_src, "interactor.cpp")
    if not os.path.isfile(interactor_cpp):
        sys.exit(f"❌ 找不到 {interactor_cpp}")

    # 1. 容器内临时目录
    tmp_dir = f"/tmp/interactor_build_{uuid.uuid4().hex[:8]}"
    run(["docker", "exec", container, "mkdir", "-p", tmp_dir])

    # 2. 仅复制 interactor.cpp → 容器                           # ← 修改
    remote_cpp = f"{tmp_dir}/interactor.cpp"              # ← 修改
    run(["docker", "cp", interactor_cpp, f"{container}:{remote_cpp}"])  # ← 修改

    # 3. 容器内编译（-I 指向临时目录即可）               # ← 修改
    include_dir = "/opt/uoj_judger/uoj_judger/include"
    compile_cmd = [
        "g++", "-O2",
        f"-I{include_dir}",        # uoj 头文件
        f"-I{tmp_dir}",            # 本地 include
        "--std=c++17", "-lm",
        "-DONLINE_JUDGE",
        "-o", f"{tmp_dir}/interactor",  # 生成的 interactor
        remote_cpp,
    ]
    run(["docker", "exec", container, *compile_cmd])

    # 4. 把生成的 interactor 拉回宿主机
    host_exe = os.path.join(host_src, "interactor")
    run(["docker", "cp", f"{container}:{tmp_dir}/interactor", host_exe])
    os.chmod(host_exe, 0o755)

    # 5. 清理
    run(["docker", "exec", container, "rm", "-rf", tmp_dir])
    print(f"✅ 编译成功 → {host_exe}")

if __name__ == "__main__":
    if not (2 <= len(sys.argv) <= 3):
        sys.exit("用法：python3 compile_checker.py /path/to/checker_folder [container_name]")
    compile_checker(sys.argv[1], sys.argv[2] if len(sys.argv) == 3 else "uoj-judger")
