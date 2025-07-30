# Install php install and clone
    git init
    git pull https://gitlab.com/ikdoeict/public/vakken/full-stack-introductory-project/labo-web-apis.git
    git remote add origin https://gitlab.com/ikdoeict/<your-name>/labo-web-apis.git
    git push -u origin master

## install docker
    docker-compose up -d
    docker-compose exec php-web bash
    composer install


## comandos for push project
    git push -u origin main


### Api 
    organisations werkt
    organisation maken  werkt 
        {
        "name": "Artevelde",
        "postcode": "9100",
        "users_id": 1,
        "users_rol": "1"
        }

    toevoegen auto increments 
    ALTER TABLE organizations MODIFY id INT NOT NULL AUTO_INCREMENT;

    query voor kanal selecteren een zien de posts 
    SELECT c.id AS channels_id, c.name AS channel_name, c.organizations_id, p.id AS post_id, p.title, p.content, p.created_at, p.users_id, p.users_rol, p.channels_id FROM channels c LEFT JOIN posts p ON c.id = p.channels_id WHERE c.id = 2;


