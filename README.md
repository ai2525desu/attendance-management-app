# 勤怠管理アプリ：attendance-management-app
<!-- 10/17Laravelプロジェクト立ち上げ時に簡易記述 -->

## 前提条件
- Gitがインストールされている
- GitHubが使用できる状態
    - SSH接続が設定済みであること
- Docker & Docker Composeを使用できる状態

## 環境構築

**Dockerビルド**
1. git clone git@github.com:ai2525desu/attendance-management-app.git
2. Docker Desktopを立ち上げる
3. docker compose up -d --build

**Laravel環境構築**
1. docker compose exec php bash
2. composer install
3. 「.env example」ファイルを「.env」ファイルに命名変更。または、新しく「.env」ファイルを作成する。
4. 「.env」ファイルの該当箇所に下記の環境変数を追加
    * DBに関する記述
    ```
    DB_CONNECTION=mysql
    DB_HOST=mysql
    DB_PORT=3306
    DB_DATABASE=laravel_db
    DB_USERNAME=laravel_user
    DB_PASSWORD=laravel_pass
    ```
    * テスト用メール送信設定(Mailhog)に関する記述
    ```
    MAIL_MAILER=smtp
    MAIL_HOST=mailhog
    MAIL_PORT=1025
    MAIL_USERNAME=null
    MAIL_PASSWORD=null
    MAIL_ENCRYPTION=null
    MAIL_FROM_ADDRESS=mailhog-attendanse@example.com
    MAIL_FROM_NAME="${APP_NAME}"
    ```
5. アプリケーションキーの作成<br>
    ``` php artisan key:generate ```
6. テーブルデータ反映のためにマイグレーションの実行<br>
    ``` php artisan migrate ```
7. ダミーデータ反映のためにシーディング実行<br>
    ``` php artisan db:seed ```

**使用するマイグレーションファイル一覧**
<!-- 作成後、内容記述 -->
* 

**シーダーファイル**
<!-- 作成後内容記述 -->
* 

<!-- 10/17 PHPUnitテスト実行できるように各ファイル記述済みの状態。記述内容確認必ず -->
## PHPUnit/テスト環境の準備と実行について
**テスト環境の準備**
1. 「.env.testing.example」を「.env.testing」に命名変更。または、新しく「.env.testing」ファイルを作成する。
    - Mailhogの内容は「.env」と変更なし。
    - アプリケーションキーは空の状態。
2. 「.env.testing」に対してアプリケーションキーを取得。
    ``` php artisan key:generate --env=testing```
※ PHPUnit 実行前に migrate や seed を手動で実行する必要はありません。
3. mysqlのコンテナ内に入る
    ``` docker compose exec mysql bash ```
4. mysqlをrootユーザーで使用する。
    ``` mysql -u root -p ```
5. passwordにrootを入力する。
    ``` root ```
6. flea_market_testのDBを作成する
    ``` CREATE DATABASE attendance_management_test; ```

**PHPUnitの実行**
1. phpコンテナに入る<br>
    ``` docker compose exec php bash ```
2. 下記のコマンドでテストを実行する<br>
    ``` php artisan test ```

<!-- Ubuntu,VSCode,Docker最終記述で確定すること -->
## 使用環境（実行環境）
- Windows 11 Home
- Ubuntu 
- VSCode 
- Docker version 
- Laravel Framework 8.83.29
- PHP 8.1
- nginx:1.21.1
- mysql:8.0.26
- Mailhog

## 権限エラー対策（Windows）
* ホストとコンテナ間のファイル権限不一致によるエラーを防ぐため、docker/php/Dockerfileにて独自ユーザー('ai2525desu')を作成し、root権限以外でLaravelを実行している
    - Dockerfile一部抜粋<br>
    ```RUN useradd -m ai2525desu```
* docker-compose.ymlにてphpの箇所に下記記載を付け加えることでユーザーIDを指定している
    - 一部抜粋
        ```
        php:
            build: ./docker/php
            user: "1000:1000"
            volumes:
            - ./src:/var/www/
        ```

<!-- ER図完成後に.pngファイル貼り付け -->
## ER図
![ER図]()

<!-- 必要URL記載すること -->
## URL
* 
* phpmyadmin:http://localhost:8080
* Mailhog Web UI:http://localhost:8025/