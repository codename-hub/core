# install composer packages, NOTE: --no-deps to not wake the bees.

# NOTE: add auth.json with private repository credentials, if required.

docker-compose -f docker-compose.unittest.yml run --no-deps unittest-php81 composer update

# alternative, locally

composer update --ignore-platform-reqs

# full run, with coverage

docker-compose -f docker-compose.unittest.yml up unittest-php81

# single run w/ deps, e.g. other containers

docker-compose -f docker-compose.unittest.yml run unittest-php81 vendor/bin/phpunit --no-coverage

# single run w/ deps, e.g. other containers + process isolation

docker-compose -f docker-compose.unittest.yml run unittest-php81 vendor/bin/phpunit --no-coverage --process-isolation

# single run w/o deps, e.g. other containers

docker-compose -f docker-compose.unittest.yml run --no-deps unittest-php81 vendor/bin/phpunit --no-coverage

# interactive

docker-compose -f docker-compose.unittest.yml run --no-deps unittest-php81 vendor/bin/phpunit --no-coverage

# stop environment afterward - especially if something went wrong during tests.

docker-compose -f docker-compose.unittest.yml stop

# destroy env

docker-compose -f docker-compose.unittest.yml down
