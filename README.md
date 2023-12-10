# bp-api run instructions

cmd

cd ${DESIRABLE_LOCATION}

git clone https://github.com/Yahavmdan/bp-api.git

cd docker

docker compose up -d

.\build.bat

.\start-in-container.bat

composer install

php artisan migrate (answer yes to create new db)
