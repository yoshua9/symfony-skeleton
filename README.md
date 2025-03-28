# Symfony Project

This is a Symfony project.

## Requirements

- PHP 8.1 or higher
- Composer
- Symfony CLI (optional but recommended)

## Installation

1. Clone the repository:

    ```sh
    git clone <repository-url>
    cd <repository-directory>
    ```

2. Install dependencies:

    ```sh
    composer install
    ```

3. Set up environment variables:

    ```sh
    cp .env .env.local
    ```

    Edit `.env.local` to configure your environment variables like 
   ```
   URL_API_RESERVATIONS
   USERNAME_API_RESERVATIONS
   PASSWORD_API_RESERVATIONS
   ```

4. Run the Symfony server:

    ```sh
    symfony server:start
    ```
   
5. Access the application:

   Open your web browser and go to `http://localhost:8000/reservations`.

## Running Tests

To run tests, use the following command:

```sh
php bin/phpunit