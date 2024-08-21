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

       /*
        **
        quase certo . só que vem 5 objetos e dentro de cada objetos vem 5 pokemons


         // Gera 5 IDs aleatórios entre 1 e 898 (total de Pokémons disponíveis na PokeAPI)
         $pokemonIds = array_unique(array_map(function() {
            return rand(1, 898);
        }, range(1, 5)));
        
        // Cria uma instância de Parallel para executar corrotinas em paralelo
        $parallel = new Parallel();

        // Adiciona as tarefas ao Parallel para buscar cada Pokémon
        foreach ($pokemonIds as $id) {
            $parallel->add(function () use ($id) {
                $url = "https://pokeapi.co/api/v2/pokemon/{$id}/";
                $response = file_get_contents($url);
                if ($response === false) {
                    return null;
                }
                return json_decode($response, true);
            });
        }

        // Executa todas as tarefas em paralelo e aguarda os resultados
        $results = $parallel->wait();

        // Filtra resultados nulos e processa os dados
        $pokemons = array_filter(array_map(function ($result) {
            if (!$result) {
                return null;
            }


            // Limita o número de movimentos retornados
            $moves = array_slice($result['moves'], 0, 5);


            return [
                'name' => $result['name'],
                'image' => $result['sprites']['front_default'],
                'moves' => array_map(function ($move) {
                    return [
                        'name' => $move['move']['name'],
                        'url' => $move['move']['url'],
                    ];
                }, $moves),
            ];
        }, $results));

        // Retorna os dados em formato JSON
        return $response->json(array_values($pokemons));
        *
        */

        // Gera um ID aleatório entre 1 e 898 (total de Pokémons disponíveis na PokeAPI)
        $pokemonId = rand(1, 898);
        
        // Cria uma instância de Parallel para executar corrotinas em paralelo
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

        // Retorna o Pokémon em formato JSON
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
