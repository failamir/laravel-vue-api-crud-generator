<?php

namespace lummy\vueApi;

use Illuminate\Console\Command;
use Str;
use Storage;
use View;
use Log;
use Artisan;

//composer dump-autoload
// To publish config file 
// php artisan vendor:publish --provider="lummy\vueApi\vueApiServiceProvider" --tag="config"

// generate Model 
// Generates a Controller (list,get,create,update,delete)
// Generates a model 
// Generates the routes
// Generates the vue.js template with axios and validation

class create extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vueapi:generate {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generated the routes, controller & Vue.js single file templates';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    
  
    
    public function createController($singular, $plural){
      
      $client = Storage::createLocalDriver(['root' => config('vueApi.controller_dir')]);
      
      // Check if file already exists. If it does ask if we want to overwrite
      if ($client->exists($plural.'Controller.php')) {    
        if (!$this->confirm($plural.'Controller.php already exists. Would you like to overwrite this controller?')){
            return false;    
        } 
      } 
        
      // Create the file
      $controllerTemplate = View::make('vueApi::controller',['name' => $singular])->render();
      $controllerTemplate = "<?php \n".$controllerTemplate." ?>";
      $client->put($plural.'Controller.php', $controllerTemplate );

      return;
      
    }
    
    
    public function createVueListTemplate($singular,$plural){
      
      $client = Storage::createLocalDriver(['root' => config('vueApi.vue_files_dir')]);
      
      // Check if file already exists. If it does ask if we want to overwrite
      if ($client->exists($plural.'-list.vue')) {
        if (!$this->confirm($plural.'-list.vue already exists. Would you like to overwrite this component?')) {
          return false;
        }
      } 
      
      // Create the file
      $vueTemplate = View::make('vueApi::vue-list',['singular' => $singular,'plural'=>$plural])->render();
      $client->put($plural.'-list.vue', $vueTemplate );
      
      return;
      
    }
    
    public function createRoutes($singular, $plural){
      
      $client = Storage::createLocalDriver(['root' => config('vueApi.routes_dir')]);
      
      $routes = "\nRoute::get('".$plural."', '".$plural."Controller@list');\n";
      $routes .= "Route::get('".$plural."/{id}', '".$plural."Controller@get');\n";
      $routes .= "Route::post('".$plural."', '".$plural."Controller@create');\n";
      $routes .= "Route::put('".$plural."/{id}', '".$plural."Controller@update');\n";
      $routes .= "Route::delete('".$plural."/{id}', '".$plural."Controller@delete');\n";
      
      if ($client->exists(config('vueApi.routes_file'))) {
        $routeFile = $client->get('/'.config('vueApi.routes_file'));
        $appendedRoutes = $routeFile.$routes;
        $client->put(config('vueApi.routes_file'), $appendedRoutes);
      } else {
        $routeFile = $client->get('/'.config('vueApi.routes_file'));
        $client->put(config('vueApi.routes_file'), $routes);
      }
      
      
      
      
    }
    
    
    

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
      
        //Artisan::call('migrate');
        
        $singular = Str::camel($this->argument('model'));
        $singular = Ucfirst(Str::singular($singular));
        $plural = Ucfirst(Str::plural($singular));
        
        $this->createRoutes($singular, $plural);
        $this->createController($singular, $plural);
        $this->createVueListTemplate($singular, $plural);
        
        //Artisan::call('make:migration create'.$plural.'_table');
        
        return $this->info('Created '.$singular.'Controller.php, '.$singular.'.vue and the routes in '.config('vueApi.routes_file'));
    
    }
}
