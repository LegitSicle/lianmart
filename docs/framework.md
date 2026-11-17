# Sarite Framework

## Web-Related Classes

### Request

This is a wrapper class for the super globals `$_GET`, `$_POST`, `$_FILES`, and `$_SERVER`. It allows filters when retrieving from `$_FILES`. Every controller receives a reference of the object created from the `Application.class`.

### AJAXRequest

This is a wrapper class for incoming `fetch()` from the Javascript front-end. The JSON from the incoming request is first filtered and validated before being given through `getJson()`.

### Response

This is a builder class for returning a response from controllers. The returned response is ran by the `Application.class`. It also allows the sending of JSON back to any valid Javascript fetch.

### Route

This is a builder class for a neater way of creating a route for the router.

### Router

Its main objective is to keep track of all registered routes and provide the appropriate controller designated for that endpoint. It also allows middleware protection from the built `Route.class` passed onto it.

### Session

This is a wrapper class for the super global `$_SESSION`. It allows flash and timed variables to be stored.

### Viewer

This is a utility class for retrieving the HTML that is used for the `Response.class` to echo.

## Database-Related Classes

### Database

Mainly used for migrations and initializing the PDO connection to the database. Any SQL querying happens in the `SQLStatement.class`.

### SQLStatement

Class for easier SQL querying using method chaining.

### Model

Mainly used as a validator class

### DatabaseModel

Is the PHP representation of a database table. It inherits from `Model.class` with additional availabled rules.