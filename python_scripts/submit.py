import json
import pymysql # 或其他数据库驱动
import zipfile
import os
import random
import string
from pathlib import Path

class UOJSubmitter:
    def __init__(self, db_config):
        """
        初始化数据库连接
        """
        try:
            self.connection = pymysql.connect(**db_config, charset='utf8mb4')
            self.cursor = self.connection.cursor()
            print("✓ 数据库连接成功")
        except Exception as e:
            print(f"✗ 数据库连接失败: {e}")
            self.connection = None

    # --- 核心: UOJ 文件名/目录生成逻辑 (来自第一个脚本) ---
    # 使用 @staticmethod 表示这些方法是静态的，它们不依赖于类的实例状态 (self)
    
    @staticmethod
    def _uoj_rand(l, r):
        return random.randint(l, r)

    @staticmethod
    def _uoj_rand_string(length, charset=string.ascii_letters + string.digits):
        return ''.join(random.choice(charset) for _ in range(length))

    @staticmethod
    def _uoj_rand_avaiable_file_name(directory: Path, storage_path: Path):
        while True:
            file_name = UOJSubmitter._uoj_rand_string(20)
            # 直接在 pathlib.Path 对象上进行拼接
            full_path = storage_path / directory / file_name
            if not full_path.exists():
                # 返回相对于 storage_path 的路径
                return directory / file_name

    @staticmethod
    def _uoj_rand_avaiable_submission_file_name(storage_path: Path):
        num = UOJSubmitter._uoj_rand(1, 10000)
        submission_dir = Path("submission") / str(num)
        
        full_submission_path = storage_path / submission_dir
        full_submission_path.mkdir(parents=True, exist_ok=True)
        
        # 返回一个 pathlib.Path 对象, 例如: "submission/1234/randomstring..."
        return UOJSubmitter._uoj_rand_avaiable_file_name(submission_dir, storage_path)

    # ----------------------------------------------------------------
    def get_problem_is_hidden(self, problem_id):
        try:
            self.cursor.execute("SELECT is_hidden FROM problems WHERE id = %s", (problem_id,))
            result = self.cursor.fetchone()
            return result[0] if result else 0
        except Exception as e:
            print(f"查询 is_hidden 失败: {e}")
            return 0
            
    def submit_code(self, problem_id, username, code, language, storage_path: str):
        if not self.connection:
            print("✗ 无法提交，数据库未连接。")
            return None

        # 将字符串路径转换为 pathlib.Path 对象，以便进行现代化的路径操作
        storage_path_obj = Path(storage_path).expanduser()

        # --- 结合点 1: 使用 UOJ 的目录和文件名生成逻辑 ---
        # 不再使用 uuid，而是调用我们上面集成的 UOJ 核心方法
        relative_file_path = self._uoj_rand_avaiable_submission_file_name(storage_path_obj)
        zip_file_relative_path = str(relative_file_path) # 数据库中存储的是带 .zip 的字符串
        zip_file_full_path = storage_path_obj / zip_file_relative_path # 实际操作文件用完整路径
        
        internal_filename = "answer.code" # UOJ 风格的内部文件名

        try:
            # 确保 zip 文件所在的目录存在
            zip_file_full_path.parent.mkdir(parents=True, exist_ok=True)
            with zipfile.ZipFile(zip_file_full_path, 'w', zipfile.ZIP_DEFLATED) as zf:
                zf.writestr(internal_filename, code.encode('utf-8'))
        except Exception as e:
            print(f"✗ 创建 Zip 文件失败: {e}")
            return None

        # --- 结合点 2: `content` 字典现在使用 UOJ 风格的相对路径 ---
        content = {
            'file_name': "/" + zip_file_relative_path, # 例如: "submission/1234/xyz.zip"
            'config': [
                ['answer_language', language],
                ['problem_id', str(problem_id)]
            ]
        }

        content_json = json.dumps(content)
        # --- 步骤 3: 准备并执行数据库插入 (与原脚本一致) ---
        tot_size = len(code.encode('utf-8'))
        result_json = json.dumps({'status': 'Waiting'})
        is_hidden = self.get_problem_is_hidden(problem_id)
        
        sql = """
        INSERT INTO submissions (
            problem_id, submit_time, submitter, content, language, 
            tot_size, status, result, is_hidden
        ) VALUES (%s, NOW(), %s, %s, %s, %s, %s, %s, %s)
        """
        
        try:
            self.cursor.execute(sql, (
                problem_id, username, content_json, language,
                tot_size, 'Waiting', result_json, is_hidden
            ))
            self.connection.commit()
            submission_id = self.cursor.lastrowid
            print(f"✓ 提交成功！")
            print(f"  - 提交 ID: {submission_id}")
            print(f"  - Zip 文件: {zip_file_full_path}")
            return submission_id
        except Exception as e:
            self.connection.rollback()
            if os.path.exists(zip_file_full_path):
                os.remove(zip_file_full_path)
            print(f"✗ 数据库插入失败: {e}")
            return None

    def close(self):
        if self.connection:
            self.connection.close()
            print("✓ 数据库连接已关闭")

# --- 使用示例 ---
if __name__ == '__main__':
    # 1. 请替换为您的数据库配置
    db_config = {
        'host': 'localhost',
        'port': 3306,
        'user': 'root',
        'password': 'root',
        'database': 'app_uoj233',
    }

    # 2. 指定 UOJ 用于存放提交的目录，脚本会自动创建子目录
    # '~/...' 会被自动展开为你的 home 目录
    submission_storage_path = '~/UOJ-System/uoj_data/web/storage/' 

    # 3. 创建提交者实例
    submitter = UOJSubmitter(db_config)

    # 4. 准备提交数据
    problem_id = 1
    username = 'admin'
    cpp_code = """
#include <iostream>
int main() {
    int a, b;
    std::cin >> a >> b;
    std::cout << a + b << std::endl;
    return 0;
}
"""
    language = 'C++17'

    # 5. 执行提交
    submitter.submit_code(
        problem_id=problem_id,
        username=username,
        code=cpp_code,
        language=language,
        storage_path=submission_storage_path
    )

    # 6. 关闭连接
    submitter.close()