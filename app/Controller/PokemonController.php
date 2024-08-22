<?php
namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;
// use Hyperf\Utils\Coroutine;

use Hyperf\Coroutine\Parallel;

// use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

/**
 * @AutoController()
 */
class PokemonController
{
    public function getPokemons(ResponseInterface $response)
    {
        $pokemonIds = array_rand(range(1, 898), 5);
        
        $parallel = new Parallel();

        // Adiciona as tarefas ao Parallel para buscar cada Pokémon
        foreach ($pokemonIds as $id) 
        {
            $parallel->add(function () use ($id) 
            {
                $url = "https://pokeapi.co/api/v2/pokemon/{$id}/";
                return json_decode(file_get_contents($url), true);
            });
        }

        // Executa todas as tarefas e aguarda os resultados
        $results = $parallel->wait();

        $pokemons = array_map(function ($result) {
            return [
                'name' => $result['name'],
                'image' => $result['sprites']['front_default'],
                'moves' => array_slice(array_map(function ($move) {

                    $moveUrl = $move['move']['url'];
                    $moveDetalhes = file_get_contents($moveUrl);
    
                    // Verifica se a requisição 
                    $moveData = $moveDetalhes 
                        ? json_decode($moveDetalhes, true) 
                        : null;

                    return [
                        'name' => $move['move']['name'],
                        'url' => $move['move']['url'],
                        'effect' => $moveData['effect_entries'][0]['effect'] ?? 'Nenhum efeito disponível',
                    ];
                }, $result['moves']), 0, 2),
            ];
        }, $results);

        return $response->json($pokemons);
        
    }
  

    public function getAllPokemons (ResponseInterface $response) 
    {
       
        $url = 'https://pokeapi.co/api/v2/pokemon';            
        
        $responseFromApi = file_get_contents($url);

        $todo = json_decode($responseFromApi, true);

        return $response->json($todo);
    }
}
