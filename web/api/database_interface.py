import json
import pymysql # 或其他数据库驱动
import zipfile
import os
import random
import string
import sys, tempfile, shutil
from pathlib import Path

class UOJDatabaseInterface:
    def __init__(self, db_config, submission_storage_path = '~/UOJ-System/uoj_data/web/storage/'):
        """
        初始化数据库连接
        """
        self.db_config = db_config
        self.connect(db_config)

        self.submission_storage_path = submission_storage_path

    def connect(self, db_config):
        """
        连接到数据库
        """
        try:
            self.connection = pymysql.connect(**db_config, charset='utf8mb4')
            self.cursor = self.connection.cursor()
            print("✓ 数据库连接成功", file=sys.stderr, flush=True)
        except Exception as e:
            print(f"✗ 数据库连接失败: {e}")
            self.connection = None

    def reconnect(self):
        """
        重新连接到数据库
        """
        self.connect(self.db_config)

    @staticmethod
    def _uoj_rand(l, r):
        return random.randint(l, r)

    @staticmethod
    def _uoj_rand_string(length, charset=string.ascii_letters + string.digits):
        return ''.join(random.choice(charset) for _ in range(length))

    @staticmethod
    def _uoj_rand_avaiable_file_name(directory: Path, storage_path: Path):
        while True:
            file_name = UOJDatabaseInterface._uoj_rand_string(20)
            # 直接在 pathlib.Path 对象上进行拼接
            full_path = storage_path / directory / file_name
            if not full_path.exists():
                # 返回相对于 storage_path 的路径
                return directory / file_name

    @staticmethod
    def _uoj_rand_avaiable_submission_file_name(storage_path: Path):
        num = UOJDatabaseInterface._uoj_rand(1, 10000)
        submission_dir = Path("submission") / str(num)
        
        full_submission_path = storage_path / submission_dir
        full_submission_path.mkdir(parents=True, exist_ok=True)
        
        # 返回一个 pathlib.Path 对象, 例如: "submission/1234/randomstring..."
        return UOJDatabaseInterface._uoj_rand_avaiable_file_name(submission_dir, storage_path)

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
            zip_file_full_path.parent.mkdir(mode=0o777, parents=True, exist_ok=True)
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
            print(f"✓ 提交成功！", file=sys.stderr, flush=True)
            print(f"  - 提交 ID: {submission_id}", file=sys.stderr, flush=True)
            print(f"  - Zip 文件: {zip_file_full_path}", file=sys.stderr, flush=True)
            return submission_id
        except Exception as e:
            self.connection.rollback()
            if os.path.exists(zip_file_full_path):
                os.remove(zip_file_full_path)
            print(f"✗ 数据库插入失败: {e}")
            return None

    def new_problem(self, title, is_hidden=True, submission_requirement=None):
        if not self.connection:
            print("✗ 无法添加新题目，数据库未连接。")
            return None

        try:
            with self.cursor as cur:
                # 插入 problems 表
                cur.execute("INSERT INTO problems (title, is_hidden, submission_requirement) VALUES (%s, %s, %s)",
                            (title, is_hidden, json.dumps(submission_requirement or {})))
                problem_id = self.connection.insert_id()

                # 插入 problems_contents 表
                cur.execute("INSERT INTO problems_contents (id, statement, statement_md) VALUES (%s, %s, %s)",
                            (problem_id, '', ''))

                requirement = [{'name': 'answer', 'type': 'source code', 'file_name': 'answer.code'}]
    
                cur.execute("UPDATE problems SET submission_requirement = %s WHERE id = %s",
                            (json.dumps(requirement), problem_id))

            self.connection.commit()
            print(f"成功添加题目 ID: {problem_id}", file=sys.stderr, flush=True)
            return problem_id
        except Exception as e:
            self.connection.rollback()
            print(f"✗ 添加新题目失败: {e}")
            return None

    def fetch_result(self, submission_id):
        """
        获取提交结果
        """
        if not self.connection:
            print("✗ 无法获取结果: 数据库连接未建立")
            return None
        
        try:
            self.cursor.execute("SELECT result FROM submissions WHERE id = %s", (submission_id,))
            result = self.cursor.fetchone()
            if result:
                json_str = result[0].decode('utf-8')
                res = json.loads(json_str)
                
                return json.loads(json_str)
            else:
                print(f"✗ 提交 ID {submission_id} 不存在")
                return None
        except Exception as e:
            print(f"✗ 获取提交结果失败: {e}")
            return None

    def id_verify(self, problem_id):
        if not self.connection:
            print("✗ 无法验证题目 ID，数据库未连接。")
            return False

        try:
            with self.cursor as cur:
                cur.execute("SELECT COUNT(*) FROM problems WHERE id = %s", (problem_id,))
                count = cur.fetchone()[0]
                return count > 0
        except Exception as e:
            print(f"✗ 验证题目 ID 失败: {e}")
            return False

    def close(self):
        if self.connection:
            self.connection.close()
            print("✓ 数据库连接已关闭", file=sys.stderr, flush=True)


def create_zip_with_folder(source_dir, zip_path):
    """
    创建包含源文件夹本身的zip文件。
    """
    source_path = Path(source_dir).expanduser()
    zip_path = Path(zip_path).expanduser()
    
    with zipfile.ZipFile(zip_path, 'w', zipfile.ZIP_DEFLATED) as zipf:
        folder_name = source_path.name
        for file_path in source_path.rglob('*'):
            if file_path.is_file():
                arcname = Path(folder_name).expanduser() / file_path.relative_to(source_path)
                zipf.write(file_path, arcname)

def sync_problem(source_folder, problem_id, target_base):
    source_folder = Path(source_folder).expanduser().resolve()
    target_base = Path(target_base).expanduser().resolve()

    if not source_folder.is_dir():
        print(f"❌ 错误: 源文件夹 {source_folder} 不存在或不是一个目录。")
        sys.exit(1)
    
    if not problem_id.isdigit():
        print(f"❌ 错误: 题目ID '{problem_id}' 必须是数字。")
        sys.exit(1)

    target_folder = target_base / problem_id
    target_zip = target_base / f"{problem_id}.zip"

    try:
        print("--- 开始清理目标位置 ---", file=sys.stderr, flush=True)
        if target_folder.exists():
            shutil.rmtree(target_folder)
            print(f"🗑️ 已删除旧的目标文件夹: {target_folder}", file=sys.stderr, flush=True)
        
        if target_zip.exists():
            target_zip.unlink()
            print(f"🗑️ 已删除旧的目标ZIP: {target_zip}", file=sys.stderr, flush=True)

        with tempfile.TemporaryDirectory(prefix=f"uoj_{problem_id}_") as temp_dir_str:
            temp_dir = Path(temp_dir_str)
            print(f"\n--- 在安全的临时目录中操作: {temp_dir} ---", file=sys.stderr, flush=True)

            work_folder = temp_dir / problem_id
            
            # 【修改点 1】定义临时zip文件的路径
            temp_zip_path = temp_dir / f"{problem_id}.zip"
            
            print(f"📂 复制: {source_folder} -> {work_folder}", file=sys.stderr, flush=True)
            shutil.copytree(source_folder, work_folder)
            
            # 【修改点 2】在临时目录中创建zip文件
            print(f"📦 在临时目录中创建ZIP: {temp_zip_path}", file=sys.stderr, flush=True)
            create_zip_with_folder(work_folder, temp_zip_path)
            
            # 【修改点 3】将临时zip文件移动到最终位置
            print(f"🚚 移动ZIP文件: {temp_zip_path} -> {target_zip}", file=sys.stderr, flush=True)
            shutil.move(str(temp_zip_path), str(target_zip))

            # 移动文件夹（这部分逻辑不变）
            print(f"🚚 移动文件夹: {work_folder} -> {target_folder}", file=sys.stderr, flush=True)
            shutil.move(str(work_folder), str(target_folder))
        
        print("\n--- 操作完成，验证结果 ---", file=sys.stderr, flush=True)
        if not target_folder.exists() or not target_zip.exists():
             raise Exception("验证失败，目标文件或文件夹未成功创建！")

        print(f"\n✅ 成功!", file=sys.stderr, flush=True)
        print(f"  -> 文件夹位置: {target_folder}", file=sys.stderr, flush=True)
        print(f"  -> ZIP文件位置: {target_zip}", file=sys.stderr, flush=True)
        
        print(f"\n📋 ZIP文件内容预览:", file=sys.stderr, flush=True)
        with zipfile.ZipFile(target_zip, 'r') as zipf:
            file_list = zipf.namelist()
            for filename in file_list[:10]:
                print(f"     {filename}", file=sys.stderr, flush=True)
            if len(file_list) > 10:
                print(f"     ... 还有 {len(file_list) - 10} 个文件", file=sys.stderr, flush=True)

    except Exception as e:
        print(f"\n❌ 处理过程中发生严重错误: {e}")
        import traceback
        traceback.print_exc()
        sys.exit(1)