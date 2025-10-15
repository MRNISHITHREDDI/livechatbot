# Use the official PHP 8 image with an Apache server
FROM php:8.1-apache

# Copy the bot script into the web server's directory
COPY index.php /var/www/html/index.php

# Expose port 80 to the outside world
EXPOSE 80
