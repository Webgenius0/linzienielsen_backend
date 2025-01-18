# Mirko86z

## Installation

To Install & Run This Project, Follow These Steps:

1. Clone the repository:

    ```sh
    git clone https://github.com/Webgenius0/linzienielsen_backend.git
    ```

2. Navigate to the project directory:

    ```sh
    cd mirko86z_backend
    ```

3. Install project dependencies:

    ```sh
    composer install
    ```

4. Copy the `.env.example` file to `.env`:

    ```sh
    cp .env.example .env
    ```

5. Open the `.env` file and configure your database connection:
   - Set the `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` to match your local database settings.

6. Generate the application key:

    ```sh
    php artisan key:generate
    ```

7. Run database migrations:

    ```sh
    php artisan migrate
    ```

8. Create a symbolic link for the storage:

    ```sh
    php artisan storage:link
    ```

9. Seed the database with initial data:

    ```sh
    php artisan db:seed
    ```

10. Optimize the application:

    ```sh
    php artisan optimize
    ```

11. If the project uses JWT authentication, generate the JWT secret key:

    ```sh
    php artisan jwt:secret
    ```

12. Start the development server:

    ```sh
    php artisan serve
    ```
13. Start the queue job:

    ```sh
    php artisan queue:work
    ```
You can now access the application at `http://localhost:8000`.
