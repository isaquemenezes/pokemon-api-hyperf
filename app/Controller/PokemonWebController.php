<?php

namespace App\Controller;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\Coroutine\Parallel;
use Psr\Http\Message\ResponseInterface;

/**
 * @Controller(prefix="pokemons")
 */
class PokemonController
{
    /**
     * @GetMapping(path="/random")
     */
    public function getRandomPokemons(): ResponseInterface
    {
        $parallel = new Parallel();
        $pokemonIds = array_rand(range(1, 898), 5); // 898 é o número total de pokemons na PokeAPI

        foreach ($pokemonIds as $id) {
            $parallel->add(function () use ($id) {
                $url = "https://pokeapi.co/api/v2/pokemon/{$id}/";
                return json_decode(file_get_contents($url), true);
            });
        }

        $results = $parallel->wait();

        $pokemons = array_map(function ($result) {
            return [
                'name' => $result['name'],
                'image' => $result['sprites']['front_default'],
                'moves' => array_map(function ($move) {
                    return [
                        'name' => $move['move']['name'],
                        'url' => $move['move']['url'],
                    ];
                }, $result['moves']),
            ];
        }, $results);

        return response()->json($pokemons);
    }
}
