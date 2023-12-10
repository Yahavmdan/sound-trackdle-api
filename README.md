# bp-api run instructions

cmd

cd ${DESIRABLE_LOCATION}

git clone https://github.com/Yahavmdan/bp-api.git

cd bp-api

cd docker

docker compose up -d

.\build.bat

.\start-in-container.bat

composer install

php artisan migrate (answer yes to create new db)


# seed options:

php artisan db:seed --class=TeachersTableSeeder

php artisan db:seed --class=StudentsTableSeeder

php artisan db:seed --class=PeriodsTableSeeder

# test instructions: (all routes and resource routes has tests)

php artisan test
