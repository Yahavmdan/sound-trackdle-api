<?php

namespace Database\Seeders;

use App\Helpers\Helpers;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MoviesSeeder extends Seeder
{

    private function getFileContent(string $fileName): ?string
    {
        $filePath = storage_path('app/public/mp3/' . $fileName . '.mp3');

        if (file_exists($filePath)) {
            return $filePath;
        }
        return null;
    }

    public function run(): void
    {

        $movies = [
            ['file_path' => '', 'name' => 'Avatar', 'year' => 2009, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'Titanic', 'year' => 1997, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'Star Wars: The Force Awakens', 'year' => 2015, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'Avengers: Endgame', 'year' => 2019, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'The Lion King', 'year' => 2019, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'The Dark Knight', 'year' => 2008, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'Harry Potter', 'year' => 2001, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'Jurassic Park', 'year' => 1993, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'The Lord of the Rings: The Return of the King', 'year' => 2003, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'Finding Nemo', 'year' => 2003, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'The Lion King', 'year' => 1994, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'Forrest Gump', 'year' => 1994, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'The Matrix', 'year' => 1999, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'The Avengers', 'year' => 2012, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'Pulp Fiction', 'year' => 1994, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'The Lord of the Rings: The Fellowship of the Ring', 'year' => 2001, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'The Godfather', 'year' => 1972, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'Spider-Man: Homecoming', 'year' => 2017, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'Inception', 'year' => 2010, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'Shrek', 'year' => 2001, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'The Incredibles', 'year' => 2004, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'The Shawshank Redemption', 'year' => 1994, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'The Silence of the Lambs', 'year' => 1991, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'The Departed', 'year' => 2006, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'Gladiator', 'year' => 2000, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'The Dark Knight Rises', 'year' => 2012, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'Toy Story', 'year' => 1995, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'Beauty and the Beast', 'year' => 1991, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'The Sixth Sense', 'year' => 1999, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'The Martian', 'year' => 2015, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'Deadpool', 'year' => 2016, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'Interstellar', 'year' => 2014, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'Gravity', 'year' => 2013, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'Jaws', 'year' => 1975, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'Frozen', 'year' => 2013, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'The Revenant', 'year' => 2015, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'The Prestige', 'year' => 2006, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'Jurassic World', 'year' => 2015, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'Batman Begins', 'year' => 2005, 'is_recently_played' => 0],
            ['file_path' => '', 'name' => 'Iron Man', 'year' => 2008, 'is_recently_played' => 0],
        ];

        foreach ($movies as $movie) {
            $movie['file_path'] = self::getFileContent(Helpers::toSnakeCase($movie['name']));

            DB::table('files')->insert([
                'name' => $movie['name'],
                'year' => $movie['year'],
                'file_path' => $movie['file_path'],
                'is_recently_played' => $movie['is_recently_played'],
                'type' => 'movie',
            ]);
        }
    }
}


