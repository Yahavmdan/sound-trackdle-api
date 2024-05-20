<?php

namespace Database\Seeders;

use App\Helpers\Helpers;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MoviesSeeder extends Seeder
{

    public function run(): void
    {
        $movies = [
            ['file_path' => '', 'name' => 'Avatar', 'year' => 2009, 'main_actor' => 'Sam Worthington', 'plot' => 'A paraplegic marine on an alien planet becomes torn between following his orders and protecting the world he feels is his home.'],
            ['file_path' => '', 'name' => 'Titanic', 'year' => 1997, 'main_actor' => 'Leonardo DiCaprio', 'plot' => 'A young man and woman from different social classes fall in love aboard the ill-fated R.M.S. *'],
            ['file_path' => '', 'name' => 'Star Wars: The Force Awakens', 'year' => 2015, 'main_actor' => 'Daisy Ridley', 'plot' => 'A new threat arises as the Resistance fights to restore peace in the galaxy.'],
            ['file_path' => '', 'name' => 'Avengers: Endgame', 'year' => 2019, 'main_actor' => 'Robert Downey Jr.', 'plot' => 'The Avengers assemble once more to reverse the chaos caused by Thanos.'],
            ['file_path' => '', 'name' => 'The Lion King', 'year' => 2019, 'main_actor' => 'Donald Glover', 'plot' => 'A young lion prince flees his kingdom only to learn the true meaning of responsibility and bravery.'],
            ['file_path' => '', 'name' => 'The Dark Knight', 'year' => 2008, 'main_actor' => 'Christian Bale', 'plot' => 'Batman faces the Joker, a criminal mastermind who seeks to create chaos in Gotham City.'],
            ['file_path' => '', 'name' => 'Harry Potter', 'year' => 2001, 'main_actor' => 'Daniel Radcliffe', 'plot' => 'A young boy discovers he is a wizard on his eleventh birthday and attends Hogwarts School of Witchcraft and Wizardry.'],
            ['file_path' => '', 'name' => 'Jurassic Park', 'year' => 1993, 'main_actor' => 'Sam Neill', 'plot' => 'A theme park showcasing genetically engineered dinosaurs turns deadly when the creatures escape.'],
            ['file_path' => '', 'name' => 'The Lord of the Rings: The Return of the King', 'year' => 2003, 'main_actor' => 'Elijah Wood', 'plot' => 'The final battle for Middle-earth begins as Frodo and Sam approach Mount Doom.'],
            ['file_path' => '', 'name' => 'Finding Nemo', 'year' => 2003, 'main_actor' => 'Albert Brooks', 'plot' => 'A clownfish embarks on a journey to find his son who is captured in the Great Barrier Reef.'],
            ['file_path' => '', 'name' => 'The Lion King', 'year' => 1994, 'main_actor' => 'Matthew Broderick', 'plot' => 'A lion cub prince flees his kingdom only to learn the true meaning of responsibility and bravery.'],
            ['file_path' => '', 'name' => 'Forrest Gump', 'year' => 1994, 'main_actor' => 'Tom Hanks', 'plot' => 'A man with a low IQ recounts the early years of his life when he was unknowingly involved in important historical events.'],
            ['file_path' => '', 'name' => 'The Matrix', 'year' => 1999, 'main_actor' => 'Keanu Reeves', 'plot' => 'A hacker discovers the reality he lives in is a simulated reality created by machines.'],
            ['file_path' => '', 'name' => 'The Avengers', 'year' => 2012, 'main_actor' => 'Robert Downey Jr.', 'plot' => 'Earth\’s mightiest heroes must come together to stop Loki and his alien army from enslaving humanity.'],
            ['file_path' => '', 'name' => 'Pulp Fiction', 'year' => 1994, 'main_actor' => 'John Travolta', 'plot' => 'The lives of two mob hitmen, a boxer, a gangster\'s wife, and a pair of diner bandits intertwine in four tales of violence and redemption.'],
            ['file_path' => '', 'name' => 'The Lord of the Rings: The Fellowship of the Ring', 'year' => 2001, 'main_actor' => 'Elijah Wood', 'plot' => 'A young hobbit sets out with eight companions to destroy the powerful One Ring.'],
            ['file_path' => '', 'name' => 'The Godfather', 'year' => 1972, 'main_actor' => 'Marlon Brando', 'plot' => 'The aging patriarch of an organized crime dynasty transfers control of his clandestine empire to his reluctant son.'],
            ['file_path' => '', 'name' => 'Spider-Man: Homecoming', 'year' => 2017, 'main_actor' => 'Tom Holland', 'plot' => 'Peter Parker balances his life as an ordinary high school student with his superhero alter-ego Spider-Man.'],
            ['file_path' => '', 'name' => 'Inception', 'year' => 2010, 'main_actor' => 'Leonardo DiCaprio', 'plot' => 'A thief who enters the dreams of others to steal secrets must perform an impossible heist.'],
            ['file_path' => '', 'name' => 'Shrek', 'year' => 2001, 'main_actor' => 'Mike Myers', 'plot' => 'An ogre and a talking donkey embark on a quest to rescue a princess from a tower guarded by a dragon.'],
            ['file_path' => '', 'name' => 'The Incredibles', 'year' => 2004, 'main_actor' => 'Craig T. Nelson', 'plot' => 'A family of undercover superheroes, while trying to live the quiet suburban life, are forced into action to save the world.'],
            ['file_path' => '', 'name' => 'The Shawshank Redemption', 'year' => 1994, 'main_actor' => 'Tim Robbins', 'plot' => 'Two imprisoned men bond over several years, finding solace and eventual redemption through acts of common decency.'],
            ['file_path' => '', 'name' => 'The Silence of the Lambs', 'year' => 1991, 'main_actor' => 'Jodie Foster', 'plot' => 'A young F.B.I. cadet must confide in an incarcerated and manipulative killer to receive his help in catching another serial killer.'],
            ['file_path' => '', 'name' => 'The Departed', 'year' => 2006, 'main_actor' => 'Leonardo DiCaprio', 'plot' => 'An undercover cop and a mole in the police attempt to identify each other while infiltrating an Irish gang in Boston.'],
            ['file_path' => '', 'name' => 'Gladiator', 'year' => 2000, 'main_actor' => 'Russell Crowe', 'plot' => 'A former Roman General sets out to exact vengeance against the corrupt emperor who murdered his family and sent him into slavery.'],
            ['file_path' => '', 'name' => 'The Dark Knight Rises', 'year' => 2012, 'main_actor' => 'Christian Bale', 'plot' => 'Eight years after the Joker\'s reign of anarchy, Batman is forced out of retirement to save Gotham City from the terrorist Bane.'],
            ['file_path' => '', 'name' => 'Toy Story', 'year' => 1995, 'main_actor' => 'Tom Hanks', 'plot' => 'A cowboy doll is profoundly threatened and jealous when a new spaceman figure supplants him as top toy in a boy\'s room.'],
            ['file_path' => '', 'name' => 'Beauty and the Beast', 'year' => 1991, 'main_actor' => 'Paige O\'Hara', 'plot' => 'A young woman falls in love with a monstrous prince under a spell.'],
            ['file_path' => '', 'name' => 'The Sixth Sense', 'year' => 1999, 'main_actor' => 'Bruce Willis', 'plot' => 'A boy who communicates with spirits seeks the help of a disheartened child psychologist.'],
            ['file_path' => '', 'name' => 'The Martian', 'year' => 2015, 'main_actor' => 'Matt Damon', 'plot' => 'An astronaut becomes stranded on Mars and must rely on his ingenuity to survive.'],
            ['file_path' => '', 'name' => 'Deadpool', 'year' => 2016, 'main_actor' => 'Ryan Reynolds', 'plot' => 'A wisecracking mercenary gets experimented on and becomes immortal but ugly, and sets out to track down the man who ruined his looks.'],
            ['file_path' => '', 'name' => 'Interstellar', 'year' => 2014, 'main_actor' => 'Matthew McConaughey', 'plot' => 'A team of explorers travel through a wormhole in space in an attempt to ensure humanity\'s survival.'],
            ['file_path' => '', 'name' => 'Gravity', 'year' => 2013, 'main_actor' => 'Sandra Bullock', 'plot' => 'Two astronauts work together to survive after an accident leaves them stranded in space.'],
            ['file_path' => '', 'name' => 'Jaws', 'year' => 1975, 'main_actor' => 'Roy Scheider', 'plot' => 'A giant great white shark arrives on the shores of a New England beach resort and wreaks havoc with bloody attacks.'],
            ['file_path' => '', 'name' => 'Frozen', 'year' => 2013, 'main_actor' => 'Kristen Bell', 'plot' => 'When the newly crowned Queen Elsa accidentally uses her power to turn things into ice to curse her home in infinite winter, her sister, Anna, teams up with a mountain man, his playful reindeer, and a snowman to change the weather condition.'],
            ['file_path' => '', 'name' => 'The Revenant', 'year' => 2015, 'main_actor' => 'Leonardo DiCaprio', 'plot' => 'A frontiersman on a fur trading expedition in the 1820s fights for survival after being mauled by a bear and left for dead by members of his own hunting team.'],
            ['file_path' => '', 'name' => 'The Prestige', 'year' => 2006, 'main_actor' => 'Christian Bale', 'plot' => 'Two stage magicians engage in competitive one-upmanship in an attempt to create the ultimate stage illusion.'],
            ['file_path' => '', 'name' => 'Jurassic World', 'year' => 2015, 'main_actor' => 'Chris Pratt', 'plot' => 'A new theme park, built on the original site of * *, creates a genetically modified hybrid dinosaur, which escapes containment and goes on a killing spree.'],
            ['file_path' => '', 'name' => 'Batman Begins', 'year' => 2005, 'main_actor' => 'Christian Bale', 'plot' => 'After training with his mentor, Batman begins his fight to free crime-ridden Gotham City from corruption.'],
            ['file_path' => '', 'name' => 'Iron Man', 'year' => 2008, 'main_actor' => 'Robert Downey Jr.', 'plot' => 'After being held captive in an Afghan cave, billionaire engineer Tony Stark creates a unique weaponized suit of armor to fight evil.'],
        ];


        foreach ($movies as $movie) {
            $movie['file_path'] = Helpers::getFilePath(Helpers::toSnakeCase($movie['name']));

            DB::table('files')->insert([
                'name' => $movie['name'],
                'year' => $movie['year'],
                'file_path' => $movie['file_path'],
                'main_actor' => $movie['main_actor'],
                'plot' => $movie['plot'],
                'type' => 'movie',
            ]);
        }
    }
}


