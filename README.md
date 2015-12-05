#BlenderFarm

##Please note
**This software is not finished yet!**
Don't use it in a production environment.

##Introduction
The BlenderFarm is a tool to easily split the rendering in blender to multiple machines. This way it is possible to render multiple frames at once on different machines to speed up the rendering process of your project.

You can easily upload your project via browser and configure the frames to render, the output format and the rendering engine. When you're happy with your configuration you can start the rendering process with a single click and check the progress in your browser.

The rending is done on clients. They request rendering tasks from the manage server and use a locally installed version of [Blender](http://www.blender.org) to complete them. After completion they upload the result to the server so it can be viewed and downloaded from you.

This repository contains the software that manages the projects and coordinates the rendering tasks. It is based on the popular [Symfony PHP framework](https://symfony.com/). The software for the render nodes could be found [here](https://github.com/moschulze/blender-farm-client).

##Requirements
You need a MySQL database for BlenderFarm to store its data. The software itself has to run on an apache web server.

##Installation
###File setup
Download (or clone) the content of this repository to your server. Make sure that the required directories are writable for the web server:

```sh
chmod a+rw app/cache/ app/logs/ files/
```
 
The doc root of the web server has to point into the web/ directory.
  
###Configuration
Copy the file app/config/parameters.yml.dist to app/config/parameters.yml and open it. Now edit the parameters for the database connection so they match your setup. If you use the default database port you don't need to change this value.

###Downloading external packages
In a terminal navigate to the root folder of the installation. Here you need to run the following two commands to download the required external packages:

```sh
curl -sS https://getcomposer.org/installer | php
./composer.phar install
```

This might take a short time.

###Database setup
If you haven't created the configured database yet you can use the following console command to do so:

```sh
app/console doctrine:database:create
```
    
To create the required database schema run:

```sh
app/console doctrine:schema:create
```

###Building the frontend
To build the frontend you need [node.js](https://nodejs.org) and the node package manager npm. On Ubuntu it can be installed by running the following commands:

```sh
sudo apt-get update
sudo apt-get install nodejs npm
```

The second tool you need to install is the [Grunt](http://gruntjs.com) taskrunner CLI:

```sh
sudo npm install -g grunt-cli
```

Now that you have all the tools ready you can build the frontend. To do so simply run the following script in the project root:

```sh
./build_frontend.sh
```

###Add a user
To manage the projects you need a user account. To create one simply run

```sh
app/console user:add
```

and provide the requested data.

###Done
Congratulations, You successfully installed the manage server for your BlenderFarm!

##ToDo
- Pagination for overview page
- API-Keys for clients
- User management / Login
- Tiled rendering of stills
- Better error handling
- Installer
- Styling
- Email notifications