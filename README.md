# Media Management System

This project is a Media Management System that allows users to upload, manage, and provide feedback on media files. It includes user authentication, role-based access control, and integration with MongoDB for data storage.

## Features

- User Registration and Login
- Role-based Access Control (Admin and User)
- Media Upload and Preview
- Commenting on Media
- Updating Media Status (Admin only)
- JWT Authentication

## File Structure

```apache
#
.env
composer.json
composer.lock
public/
    assets/
    index.php
    logout.php
    profile.php
    register.php
    submit_feedback.php
    update_status.php
    uploads/      # here is where uploaded files go
README.md
src/
    Admin.php
    Annotations.php
    auth.php
    db.php
    Feedback.php
    media.php
    Messaging.php
    Performance.php
    tasks.php
    User.php
vendor/
    autoload.php
    composer/
        autoload_classmap.php
        autoload_files.php
        autoload_namespaces.php
        autoload_psr4.php
        autoload_real.php
        autoload_static.php
        ...
    firebase/
    mongodb/
    psr/
    symfony/
```

## Installation

1. Clone the repository:

   ```sh
   git clone https://github.com/whoiszacky/phpmango.git
   cd phpmango
   ```
2. Install dependencies using Composer:

   ```sh
   composer install
   ```
3. > Set up environment variables `.env` !! **Only** if you want to keep your keeps private or production
   >
4. Start your local development server:

   ```sh


   #this not needed as you u can use http://localhost:3000/public/index.php
   php -S localhost:8000 -t public
   ```

   5. Remember to install php server extention and righ clcik and start the server project

## Usage

- Visit `http://localhost:8000` in your browser to access the application.
- Register a new user or log in with existing credentials.
- Upload media files, add comments, and manage media status if you are an admin.
