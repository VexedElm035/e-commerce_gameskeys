# Requisitos: instalar nodejs y npm, comprobar que este instalado con:

```sh
node --version
npm --version
```

# Clonar el repositorio e instalar las dependencias

```sh
git clone https://github.com/VexedElm035/tienda_keys.git 
cd tienda_keys
npm install
```

# Inicializar la app
(opcional con parametros: -- --host 0.0.0.0 --port 5173)

```sh
npm run dev
```

# En diferente terminal ejecutar comando de npx tailwind

```sh
npx @tailwindcss/cli -i ./src/index.css -o ./src/output.css --watch
```

### Compile and Minify for Production

```sh
npm run build
```

# Anotaciones de clase cff

MENU:
uso de ajax para qeu no vuelva a recargar todo cada http request
poner menu como componente
tree shaking

 <!--
 
 let message = ref("probando") //para reactividad | como variables cualquiera pero cuando se actualice el valor de este elemento reactivo
let show = ref(false)
let id = ref('message-box')

const data = ref()

message.value = 'hola probando'

//computed, valor no esta definido y su valor depende de otra propiedad
let messageChange = computed(() => {
    return (show.value === false) ? 'adios probando' : 'hola probando'
})

function changeMessage() {
    message.value = 'cambiando mensaje'
}


 
 
 
  {{message}}
    {{show}}
    {{messageChange}}
    misma forma de usar eventos o funciones 
    <button 
    @click="show = !show" 
    >ola1</button>
    <button    
    v-on:click="show = !show"    
    >ola2</button>

    <div v-if="show"> se puede usar v-if o v-show 
        <p>{{message}           }</p>
    </div>
    <div v-else="show"> se puede usar v-if o v-show 
        <p>hola probando si</p>
    </div>
    Tambien existe v-if-else -->


//Instalacion de axios en vue con: 
npm install axios

//Dentro del main.js se importa, configura y usa con:
import axios from 'axios'
...
axios.defaults.baseURL = 'http://localhost:8080/api' //la url del backend en laravel
axios.defaults.headers.common['Accept'] = 'application/json'
...
app.use(axios)

//Dentro de la vista del login se hace la siguiente funcion
async function loginUser() {
  try{
    const response = await axios.post('/users', { //en axios.{metodo}, se pone get, post o demas
    //Ademas se usan las rutas de api para evitar conflictos con CORS
      params:{
        username: username.value,
        password: password.value
      },
    })
    console.log(response)
  }
  catch(error){ //si el codigo de estado que le pasamos desde el backend es diferente de uno de exito (200,201) se ejecuta el catch
    if(error.response.data.message){
      console.log("Error",error)
    }
    else{
      
    }
  }
}
//La cual se ejecutara cuando pulsemos el boton de un formulario, y lo unico que hara es una solicitud por POST a una ruta hecha en laravel que por el momento no hace nada mas que regresar un mensaje

public function store(Request $request)
    {
        $params = $request->input('params');
        return response()->json([
            'params' => $params,
        ]);
    }



////////////////////

//en modelo usuario
useapitoken()

//backend en el controlador del login de una peticion por post

$user = $params['username'];
$password = $params['password'];

if (!Auth::attemp(['email'=>$user, 'password'=>$password])) {
	return response ([
		'message' => 'wrong login',
	], 401);
};

$user = $request->$user();
$toker = $user -> createToken('authToken');
$user = Auth::user(); //cualquiera de esas dos

return response([
	'message' => 'acesso autorizado',
	'user' = $user,
	'token' => $token->plainTextToken,
], 200);


//rutas
Route::post({
}->middleware('auth:sanctum'));

//frontend vista login

headers: {
	'Authorization' : $bearer ${userStore.token}
}

userStore.token = response.data.token

if(error.response.data.message){
	console.log(error.response.data.message)
}

//store
let token = ref('');


//Instalar libreria de componentes
Primevue para vite
Zod
Vuetify
//Configurarlos
main.js
import PrimeVue from 'primevue/config';
import Aura from '@primeuix/themes/aura';


//hay que usar
FileReader()
5j!
reader.onload 
<FileUpload>
reader.onload


instale:
npm install country-flag-icons --save


npm install
npm run dev -- --host 0.0.0.0 --port 5173
npx @tailwindcss/cli -i ./src/index.css -o ./src/output.css --watch 
