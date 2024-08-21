<?php
namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\Utils\Coroutine;
// use Hyperf\Utils\Parallel;
use Hyperf\Coroutine\Parallel;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

/**
 * @AutoController()
 */
class PokemonController
{
    public function getPokemons(RequestInterface $request, ResponseInterface $response)
    {

        $pokemonId = rand(1, 898);        
     
        $parallel = new Parallel();

        // Adiciona a tarefa ao Parallel para buscar o Pokémon
        $parallel->add(function () use ($pokemonId) {
            $url = "https://pokeapi.co/api/v2/pokemon/{$pokemonId}/";
            $response = file_get_contents($url);
            if ($response === false) {
                return null;
            }
            return json_decode($response, true);
        });

        // Executa a tarefa em paralelo e aguarda o resultado
        $result = $parallel->wait()[0];

        // Processa o Pokémon e limita o número de movimentos retornados
        $pokemon = $result ? [
            'name' => $result['name'],
            'image' => $result['sprites']['front_default'],
            'moves' => array_slice(array_map(function ($move) {
                return [
                    'name' => $move['move']['name'],
                    'url' => $move['move']['url'],
                ];
            }, $result['moves']), 0, 5),
        ] : null;

       
        return $response->json($pokemon);  
        
    
        
    }

   


    public function getAllPokemons (ResponseInterface $response) {
          // URL da API do Pokémon
          $url = 'https://pokeapi.co/api/v2/pokemon';
            
          // Fazer uma chamada à API usando file_get_contents() (simples) ou Guzzle (mais robusto).
          $responseFromApi = file_get_contents($url);

          // Opcional: Decodificar o JSON retornado pela API, se necessário.
          $todo = json_decode($responseFromApi, true);

          // Retornar os dados da API como JSON.
          return $response->json($todo);
    }
}
