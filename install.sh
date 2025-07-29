#!/bin/bash

echo "Starting Laravel application setup..."

# Check for Composer
if ! command -v composer &> /dev/null
then
    echo "Composer could not be found. Please install Composer to continue."
    echo "See: https://getcomposer.org/download/"
    exit
fi

# 1. Install PHP dependencies
echo "Installing Composer dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader

# 2. Create .env file
if [ ! -f ".env" ]; then
    echo "Creating .env file..."
    cp .env.example .env
else
    echo ".env file already exists."
fi

# 3. Generate application key
echo "Generating application key..."
php artisan key:generate

# 4. Create SQLite database file
if [ ! -f "database/database.sqlite" ]; then
    echo "Creating SQLite database file..."
    touch database/database.sqlite
else
    echo "Database file already exists."
fi

# 5. Run database migrations
echo "Running database migrations..."
php artisan migrate --force

echo ""
echo "-------------------------------------"
echo "Setup complete!"
echo "You should now be able to serve the application."
echo "For example, run: php artisan serve"
echo "-------------------------------------"
