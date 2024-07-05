if [ $1 = "cobertura" ]; then
    ./vendor/bin/phpunit --coverage-cobertura "./.phpunit.coverage.cobertura"
else
    ./vendor/bin/phpunit
fi