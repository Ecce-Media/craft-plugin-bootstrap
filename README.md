#Craft Plugin Development Bootstrap
Allows you to create a bootstrapped project specifically for craft cms plugin development

##Install Environment
    composer create-project ecce-media/craft-plugin-bootstrap path
    
When then installation is finished craft will need to be setup You will need to run:

    docker-compose run web php craft setup
    
This will install the database and allow you to login and install the plugin.

##Running Environment 
To run the container run the command:
    
    docker-compose up        