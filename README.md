# LianMart Project

This is the repo for our majors final's project.

## Setup

To begin contributing to the project, you must first set up your own local database and install dependencies. Composer must be installed which is then used to download the following.

- `vlucas/phpdotenv`

For Windows developers, there is a `setup.bat` file that automates this process. However to successfully use the script, you **MUST** first implement your own `.env` file using the `.env.example` as the format. After that, just run the setup script in the `scripts` directory.

For additional info about the framework, [click this link](docs/framework.md).

## Q&A (Issues)

Q: Driver not found to initialize Database, fix?\
A: Find your php.ini file. It is typically found in C:\xampp\php in Windows or /opt/lampp/etc in Linux. Afterwards uncomment or remove the `;` in the line `;extension=pdo_mysql`.
