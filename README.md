A microservice that allows the basic functionality of a tic-tac-toe game.

This repo is made to be used with Docker Compose.

Steps usage:
 - Download or clone https://github.com/markirnanflores/nested-set-model-demo.git
 - Run "docker-compose up"
 - Start a new game by making a POST request, the json response will contain the id for the game
 - Make a PUT request with the game 'id', 'player' and 'position' parameters
 - Valid values for player are 1 and 2
 - Valid values for position are 'A1', 'A2', 'A3','B1', 'B2', 'B3','C1', 'C2', 'C3'
 