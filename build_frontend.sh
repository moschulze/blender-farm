#!/usr/bin/env bash

cd src/AppBundle/Resources/frontend;
npm install;
bower install;
grunt;
cd -;
app/console assets:install;