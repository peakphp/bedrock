# Peak\Bedrock

Create HTTP Request/Response application compatible PSR-7, PSR-11 and PSR-15.

```php
// Create app with factory
$appFactory = new ApplicationFactory();
$app = $appFactory->create('dev', new Container());

// Create StackFactory
$stackFactory = new StackFactory($app->getHandlerResolver());

// Adding multiple middlewares to application stack
$app->add([
    BootstrapMiddleware::class,
    DoStuffMiddleware::class,
    $app->get('/user/([a-zA-Z0-9]+)', [
        UserProfileHandler::class
    ]),
    $app->post('/userForm/([a-zA-Z0-9]+)', [
        AuthenticationMiddleware::class,
        UserFormHandler::class
    ]),
    LogNotFoundMiddleware::class
    PageNotFoundHandler::class
]);

// Execute the app stack
try {
    // create response emitter
    $emitter = new Emitter();
    
    // create request from globals
    $request = ServerRequestFactory::fromGlobals();
    
    // handle request and emit app stack response
    $app->run($request, $emitter);
} catch(Exception $e) {
    // overwrite app stack with error middleware
    $app->set(new DevExceptionHandler($e))
        ->run($request, $emitter);
}

```