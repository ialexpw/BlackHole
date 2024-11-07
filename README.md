# BlackHole
A simple Request Bin style application that allows you to capture, debug and inspect HTTP requests in a human-readable way. It is especially helpful when creating an application API or developing Webhooks.

BlackHole allows you to capture GET, POST, PUT, PATCH, DELETE and more HTTP requests.

## Requirements
BlackHole is lightweight and can be setup within minutes, the only requirements are a webserver and PHP. Both Apache and nginx work without issue and PHP 7+ is recommended. No database is required.

## Installation
1. Clone the repository
1. Ensure the directory has write access
1. Click to generate a random url
1. Send HTTP requests to the given url
1. Refresh the page to see information about the requests

## Screenshot
![image](https://github.com/user-attachments/assets/d5c3fdf0-52b6-42ff-b6b9-8b990a59bbde)


## API Endpoints
* View Details (no HTML): `/index.php?api&details=<code>` (linked from results page)
* View Details (with HTML tags): `/index.php?api&details=<code>&raw`
* Create New Bin: `/index.php?api&create`
