# アプリケーション名

attendance-app

勤怠管理システムを実装したLaravelプロジェクトです。

一般ユーザーは出勤・退勤・休憩管理、勤怠一覧の確認、勤怠修正申請を行うことができます。
管理者は全ユーザーの勤怠管理、勤怠修正、修正申請の承認、CSV出力を行うことができます。

## 作成者
齊藤香美

## 使用技術
- PHP 8.3.6
- Laravel 10.50.3
- MySQL 8.0
- Nginx
- Docker 29.1.3
- Docker Compose v5.3.1
- Laravel Sail
- Vite
- Alpine.js
- Laravel Fortify（認証）
- phpMyAdmin

## ER図

+-------------+
|    users    |
+-------------+
| id (PK)     |
| name        |
| email       |
| password    |
| admin_status|       |
| created_at  |
| updated_at  |
+-------------+
       |
       | 1
       |
       | N
+----------------------+
| attendance_records   |
+----------------------+
| id (PK)              |
| user_id (FK)         |
| date                 |
| clock_in             |
| clock_out            |
| comment              |
| created_at           |
| updated_at           |
+----------------------+
       |
       | 1
       |
       | N
+----------------------------+
| attendance_breaks          |
+----------------------------+
| id (PK)                    |
| attendance_record_id (FK)  |
| break_in                   |
| break_out                  |
| created_at                 |
| updated_at                 |
+----------------------------+


+-------------+
|    users    |
+-------------+
| id (PK)     |
+-------------+
       |
       | 1
       |
       | N
+----------------------------------+
| stamp_correction_requests        |
+----------------------------------+
| id (PK)                          |
| attendance_record_id (FK)        |
| user_id (FK)                     |
| requested_clock_in               |
| requested_clock_out              |
| comment                          |
| status                           |
| approved_at                      |
| approved_by (FK users.id)        |
| created_at                       |
| updated_at                       |
+----------------------------------+
       |
       | 1
       |
       | N
+----------------------------------+
| attendance_correction_breaks     |
+----------------------------------+
| id (PK)                          |
| stamp_correction_request_id (FK) |
| break_in                         |
| break_out                        |
| created_at                       |
| updated_at                       |
+----------------------------------+```

リレーションは以下の通りです。

users
 ├──< attendance_records
 │        └──< attendance_breaks
 │
 └──< stamp_correction_requests
          └──< attendance_correction_breaks

attendance_records
 └──< stamp_correction_requests

## 開発環境URL

http://localhost

## 動作環境

- Docker
- Docker Compose

※ Windowsの場合はWSL2の利用を推奨します。

## 環境構築手順

1. リポジトリをクローン
```bash
https://github.com/saito-yoshimi/attendance-app.git
```

2. **.envファイルの準備**
    `.env.example` をコピーして `.env` を作成します。
    ```bash
    cp .env.example .env
    ```
    `.env` ファイル内の以下のDB接続情報を確認・設定します。`.env.example` のデフォルト値はSail向けではないため、以下のように変更してください。

    ```ini
    DB_CONNECTION=mysql
    DB_HOST=mysql
    DB_PORT=3306
    DB_DATABASE=laravel
    DB_USERNAME=sail
    DB_PASSWORD=password
    ```
3. **Composer依存パッケージのインストール**

    プロジェクトの初回セットアップ時は、`vendor` ディレクトリが存在しないため `sail` コマンドを使用できません。
    以下のDockerコマンドを実行して、コンテナ内で `composer install` を実行します。

    ```bash
    docker run --rm \
        -u "$(id -u):$(id -g)" \
        -v "$(pwd):/var/www/html" \
        -w /var/www/html \
        laravelsail/php82-composer:latest \
        composer install --ignore-platform-reqs
    ```

4. **Laravel Sailの起動**

    以下のコマンドでDockerコンテナを起動します。

    ```bash
    ./vendor/bin/sail up -d
    ```

    > **エイリアスの設定（推奨）**
    >
    > 毎回 `./vendor/bin/sail` と入力するのは手間なので、エイリアスを設定すると便利です。
    >
    > ```bash
    > alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'
    > ```

5. **アプリケーションキーの生成**

    ```bash
    sail artisan key:generate
    ```

6. **データベースのマイグレーションと初期データ投入**

    以下のコマンドでテーブルを作成し、ダミーデータを投入します。

    ```bash
    sail artisan migrate:fresh --seed
    ```

    このコマンドの入力後、下記のエラーが表示されることがあります。

    ```bash
       Illuminate\Database\QueryException
      SQLSTATE[HY000] [1044] Access denied for user 'sail'@'%' to database 'attendance-app' (Connection: mysql, SQL: select table_name as `name`,         (data_length + index_length) as `size`, table_comment as `comment`, engine as `engine`, table_collation as `collation` from information_schema.tables where table_schema = 'attendance-app' and table_type in ('BASE TABLE', 'SYSTEM VERSIONED') order by table_name)

      at vendor/laravel/framework/src/Illuminate/Database/Connection.php:829
        825▕                     $this->getName(), $query, $this->prepareBindings($bindings), $e
        826▕                 );
        827▕             }
        828▕
      ➜ 829▕             throw new QueryException(
        830▕                 $this->getName(), $query, $this->prepareBindings($bindings), $e
        831▕             );
        832▕         }
        833▕     }

      +43 vendor frames

      44  artisan:35
          Illuminate\Foundation\Console\Kernel::handle()
    ```

    このエラーはコンテナ内にデータが残っており、エラーが生じているケースなどがあります。
    その場合は、以下のコマンドを順に実行して各コンテナを再起動して下さい。

    ```Bash
    sail down -v
    sail up -d　//コマンド実行後にSQLコンテナが立ち上がるまで時間がかかります。30秒ほどお待ちください。
    sail artisan migrate:fresh --seed
    ```

7. **フロントエンドのビルド**

    ```bash
    sail npm install
    sail npm install alpinejs
    sail npm run dev
    ```

    `npm run dev` は開発中は起動したままにしてください。

8. **アプリケーションへのアクセス**

ブラウザで以下にアクセスします。

- アプリ: http://localhost
- phpMyAdmin: http://localhost:8080
- Mailpit: http://localhost:8025

## テスト実行

PHPUnitによるテストを実装しています。

```bash
sail artisan test
```

カバレッジ付きで実行する場合:

```bash
sail artisan test --coverage
```

- Tests: 79
- Assertions: 125
- Coverage: 68.8%


## 機能一覧

**一般ユーザー**

- 会員登録
- ログイン / ログアウト
- メール認証
- 出勤
- 退勤
- 休憩開始
- 休憩終了
- 勤怠一覧表示（月次）
- 勤怠詳細表示
- 勤怠修正申請
- 修正申請一覧表示
- 修正申請詳細表示
- 勤怠レポート表示

**管理者**

- 管理者ログイン
- 全ユーザー勤怠一覧表示
- 日付別勤怠一覧表示
- スタッフ一覧表示
- スタッフ別月次勤怠一覧表示
- 勤怠詳細表示
- 勤怠修正
- 修正申請一覧表示
- 修正申請承認
- CSVエクスポート

### API

- 勤怠一覧取得
- 勤怠詳細取得
- 勤怠作成
- 勤怠更新
- 勤怠削除
- Sanctum認証

## APIエンドポイント一覧

Laravel Sanctumを利用したAPIです。一部エンドポイントは認証が必要です。
全エンドポイントは `/api/v1` プレフィックス配下に定義されています。

### エンドポイント

| HTTPメソッド | URI | 概要 |
|------------|------------|------------|
| GET | /api/v1/attendance-records | 勤怠一覧取得 |
| GET | /api/v1/attendance-records/{id} | 勤怠詳細取得 |
| POST | /api/v1/attendance-records | 勤怠登録 |
| PUT | /api/v1/attendance-records/{id} | 勤怠更新 |
| DELETE | /api/v1/attendance-records/{id} | 勤怠削除 |

### 認証が必要なエンドポイント

| HTTPメソッド | URI | 説明 |
|------------|------------|------------|
| POST | /api/v1/attendance-records | 勤怠登録 |
| PUT | /api/v1/attendance-records/{id} | 勤怠更新 |
| DELETE | /api/v1/attendance-records/{id} | 勤怠削除 |

### レスポンス例

```json
{
    "data": {
        "id": 1,
        "date": "2026-09-30",
        "clock_in": "09:00:00",
        "clock_out": "18:00:00"
    }
}
```




