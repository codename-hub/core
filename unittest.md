
# install composer packages, NOTE: --no-deps to not wake the bees.
# NOTE: add auth.json with private repository credentials, if required.
docker-compose -f docker-compose.unittest.yml run --no-deps unittest-php73 composer update

# alternative, locally
composer update --ignore-platform-reqs

# full run, no coverage
docker-compose -f docker-compose.unittest.yml up unittest-php73

# single run w/ deps, e.g. other containers
docker-compose -f docker-compose.unittest.yml run unittest-php73 vendor/bin/phpunit --no-coverage

# single run(w/o deps, e.g. other containers
docker-compose -f docker-compose.unittest.yml run --no-deps unittest-php73 vendor/bin/phpunit --no-coverage

# interactive
docker-compose -f docker-compose.unittest.yml run --no-deps unittest-php73 vendor/bin/phpunit --no-coverage

# stop environment afterwards - especially if something went wrong during tests.
docker-compose -f docker-compose.unittest.yml stop

# destroy env
docker-compose -f docker-compose.unittest.yml down
