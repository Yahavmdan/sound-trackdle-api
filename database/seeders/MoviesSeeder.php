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
            ['file_path' => '', 'name' => 'Avatar', 'year' => 2009, 'main_actor' => 'Sam Worthington', 'genre' => 'Science Fiction, Adventure'],
            ['file_path' => '', 'name' => 'Titanic', 'year' => 1997, 'main_actor' => 'Leonardo DiCaprio', 'genre' => 'Romance, Drama'],
            ['file_path' => '', 'name' => 'Star Wars: The Force Awakens', 'year' => 2015, 'main_actor' => 'Daisy Ridley', 'genre' => 'Science Fiction, Fantasy, Adventure'],
            ['file_path' => '', 'name' => 'Avengers: Endgame', 'year' => 2019, 'main_actor' => 'Robert Downey Jr.', 'genre' => 'Superhero, Science Fiction, Action, Adventure'],
            ['file_path' => '', 'name' => 'The Dark Knight', 'year' => 2008, 'main_actor' => 'Christian Bale', 'genre' => 'Superhero, Crime, Drama, Action'],
            ['file_path' => '', 'name' => 'Harry Potter', 'year' => 2001, 'main_actor' => 'Daniel Radcliffe', 'genre' => 'Fantasy, Adventure'],
            ['file_path' => '', 'name' => 'Jurassic Park', 'year' => 1993, 'main_actor' => 'Sam Neill', 'genre' => 'Science Fiction, Adventure'],
            ['file_path' => '', 'name' => 'The Lord of the Rings: The Return of the King', 'year' => 2003, 'main_actor' => 'Elijah Wood', 'genre' => 'Fantasy, Adventure, Drama'],
            ['file_path' => '', 'name' => 'Finding Nemo', 'year' => 2003, 'main_actor' => 'Albert Brooks', 'genre' => 'Animation, Adventure, Comedy'],
            ['file_path' => '', 'name' => 'The Lion King', 'year' => 1994, 'main_actor' => 'Matthew Broderick', 'genre' => 'Animation, Adventure, Drama'],
            ['file_path' => '', 'name' => 'Forrest Gump', 'year' => 1994, 'main_actor' => 'Tom Hanks', 'genre' => 'Drama, Romance'],
            ['file_path' => '', 'name' => 'The Matrix', 'year' => 1999, 'main_actor' => 'Keanu Reeves', 'genre' => 'Science Fiction, Action'],
            ['file_path' => '', 'name' => 'The Avengers', 'year' => 2012, 'main_actor' => 'Robert Downey Jr.', 'genre' => 'Superhero, Science Fiction, Action'],
            ['file_path' => '', 'name' => 'Pulp Fiction', 'year' => 1994, 'main_actor' => 'John Travolta', 'genre' => 'Crime, Drama'],
            ['file_path' => '', 'name' => 'The Lord of the Rings: The Fellowship of the Ring', 'year' => 2001, 'main_actor' => 'Elijah Wood', 'genre' => 'Fantasy, Adventure, Drama'],
            ['file_path' => '', 'name' => 'The Godfather', 'year' => 1972, 'main_actor' => 'Marlon Brando', 'genre' => 'Crime, Drama'],
            ['file_path' => '', 'name' => 'Spider-Man: Homecoming', 'year' => 2017, 'main_actor' => 'Tom Holland', 'genre' => 'Superhero, Action, Adventure'],
            ['file_path' => '', 'name' => 'Inception', 'year' => 2010, 'main_actor' => 'Leonardo DiCaprio', 'genre' => 'Science Fiction, Action, Adventure'],
            ['file_path' => '', 'name' => 'Shrek', 'year' => 2001, 'main_actor' => 'Mike Myers', 'genre' => 'Animation, Adventure, Comedy'],
            ['file_path' => '', 'name' => 'The Incredibles', 'year' => 2004, 'main_actor' => 'Craig T. Nelson', 'genre' => 'Animation, Superhero, Adventure'],
            ['file_path' => '', 'name' => 'The Shawshank Redemption', 'year' => 1994, 'main_actor' => 'Tim Robbins', 'genre' => 'Drama'],
            ['file_path' => '', 'name' => 'The Silence of the Lambs', 'year' => 1991, 'main_actor' => 'Jodie Foster', 'genre' => 'Thriller, Crime, Drama'],
            ['file_path' => '', 'name' => 'The Departed', 'year' => 2006, 'main_actor' => 'Leonardo DiCaprio', 'genre' => 'Crime, Drama, Thriller'],
            ['file_path' => '', 'name' => 'Toy Story', 'year' => 1995, 'main_actor' => 'Tom Hanks', 'genre' => 'Animation, Adventure, Comedy'],
            ['file_path' => '', 'name' => 'Beauty and the Beast', 'year' => 1991, 'main_actor' => 'Paige O\'Hara', 'genre' => 'Animation, Fantasy, Musical'],
            ['file_path' => '', 'name' => 'The Sixth Sense', 'year' => 1999, 'main_actor' => 'Bruce Willis', 'genre' => 'Thriller, Drama, Mystery'],
            ['file_path' => '', 'name' => 'The Martian', 'year' => 2015, 'main_actor' => 'Matt Damon', 'genre' => 'Science Fiction, Adventure, Drama'],
            ['file_path' => '', 'name' => 'Deadpool', 'year' => 2016, 'main_actor' => 'Ryan Reynolds', 'genre' => 'Superhero, Comedy, Action'],
            ['file_path' => '', 'name' => 'Interstellar', 'year' => 2014, 'main_actor' => 'Matthew McConaughey', 'genre' => 'Science Fiction, Drama, Adventure'],
            ['file_path' => '', 'name' => 'Gravity', 'year' => 2013, 'main_actor' => 'Sandra Bullock', 'genre' => 'Science Fiction, Thriller, Drama'],
            ['file_path' => '', 'name' => 'Jaws', 'year' => 1975, 'main_actor' => 'Roy Scheider', 'genre' => 'Thriller, Horror, Adventure'],
            ['file_path' => '', 'name' => 'Frozen', 'year' => 2013, 'main_actor' => 'Kristen Bell', 'genre' => 'Animation, Adventure, Musical'],
            ['file_path' => '', 'name' => 'The Revenant', 'year' => 2015, 'main_actor' => 'Leonardo DiCaprio', 'genre' => 'Adventure, Drama, Thriller'],
            ['file_path' => '', 'name' => 'The Prestige', 'year' => 2006, 'main_actor' => 'Christian Bale', 'genre' => 'Drama, Mystery, Thriller'],
            ['file_path' => '', 'name' => 'Jurassic World', 'year' => 2015, 'main_actor' => 'Chris Pratt', 'genre' => 'Science Fiction, Adventure, Action'],
            ['file_path' => '', 'name' => 'Batman Begins', 'year' => 2005, 'main_actor' => 'Christian Bale', 'genre' => 'Superhero, Action, Adventure'],
            ['file_path' => '', 'name' => 'Iron Man', 'year' => 2008, 'main_actor' => 'Robert Downey Jr.', 'genre' => 'Superhero, Science Fiction, Action'],
            ['file_path' => '', 'name' => '12 Angry Men', 'year' => 1957, 'main_actor' => 'Henry Fonda', 'genre' => 'Drama'],
            ['file_path' => '', 'name' => 'A Beautiful Mind', 'year' => 2001, 'main_actor' => 'Russell Crowe', 'genre' => 'Biography'],
            ['file_path' => '', 'name' => 'A Clockwork Orange', 'year' => 1971, 'main_actor' => 'Malcolm McDowell', 'genre' => 'Crime'],
            ['file_path' => '', 'name' => 'A Quiet Place', 'year' => 2018, 'main_actor' => 'Emily Blunt', 'genre' => 'Horror'],
            ['file_path' => '', 'name' => 'A Star is Born', 'year' => 2018, 'main_actor' => 'Lady Gaga', 'genre' => 'Drama'],
            ['file_path' => '', 'name' => 'Aladdin', 'year' => 1992, 'main_actor' => 'Scott Weinger', 'genre' => 'Animation'],
            ['file_path' => '', 'name' => 'Alien', 'year' => 1979, 'main_actor' => 'Sigourney Weaver', 'genre' => 'Sci-Fi'],
            ['file_path' => '', 'name' => 'Amadeus', 'year' => 1984, 'main_actor' => 'F. Murray Abraham', 'genre' => 'Biography'],
            ['file_path' => '', 'name' => 'American Beauty', 'year' => 1999, 'main_actor' => 'Kevin Spacey', 'genre' => 'Drama'],
            ['file_path' => '', 'name' => 'American History X', 'year' => 1998, 'main_actor' => 'Edward Norton', 'genre' => 'Drama'],
            ['file_path' => '', 'name' => 'American Psycho', 'year' => 2000, 'main_actor' => 'Christian Bale', 'genre' => 'Crime'],
            ['file_path' => '', 'name' => 'Anchorman: The Legend of Ron Burgundy', 'year' => 2004, 'main_actor' => 'Will Ferrell', 'genre' => 'Comedy'],
            ['file_path' => '', 'name' => 'Apocalypse Now', 'year' => 1979, 'main_actor' => 'Martin Sheen', 'genre' => 'War'],
            ['file_path' => '', 'name' => 'Arrival', 'year' => 2016, 'main_actor' => 'Amy Adams', 'genre' => 'Sci-Fi'],
            ['file_path' => '', 'name' => 'Back to the Future', 'year' => 1985, 'main_actor' => 'Michael J. Fox', 'genre' => 'Sci-Fi'],
            ['file_path' => '', 'name' => 'Batman v Superman: Dawn of Justice', 'year' => 2016, 'main_actor' => 'Ben Affleck', 'genre' => 'Action'],
            ['file_path' => '', 'name' => 'Beauty and the Beast (1991)', 'year' => 1991, 'main_actor' => 'Paige O\'Hara', 'genre' => 'Animation'],
            ['file_path' => '', 'name' => 'Black Panther', 'year' => 2018, 'main_actor' => 'Chadwick Boseman', 'genre' => 'Action'],
            ['file_path' => '', 'name' => 'Blade Runner', 'year' => 1982, 'main_actor' => 'Harrison Ford', 'genre' => 'Sci-Fi'],
            ['file_path' => '', 'name' => 'Blazing Saddles', 'year' => 1974, 'main_actor' => 'Cleavon Little', 'genre' => 'Comedy'],
            ['file_path' => '', 'name' => 'Braveheart', 'year' => 1995, 'main_actor' => 'Mel Gibson', 'genre' => 'Biography'],
            ['file_path' => '', 'name' => 'Breakfast at Tiffany\'s', 'year' => 1961, 'main_actor' => 'Audrey Hepburn', 'genre' => 'Romance'],
            ['file_path' => '', 'name' => 'Bridesmaids', 'year' => 2011, 'main_actor' => 'Kristen Wiig', 'genre' => 'Comedy'],
            ['file_path' => '', 'name' => 'Casablanca', 'year' => 1942, 'main_actor' => 'Humphrey Bogart', 'genre' => 'Romance'],
            ['file_path' => '', 'name' => 'Casino Royale', 'year' => 2006, 'main_actor' => 'Daniel Craig', 'genre' => 'Action'],
            ['file_path' => '', 'name' => 'Catch Me If You Can', 'year' => 2002, 'main_actor' => 'Leonardo DiCaprio', 'genre' => 'Biography'],
            ['file_path' => '', 'name' => 'Chinatown', 'year' => 1974, 'main_actor' => 'Jack Nicholson', 'genre' => 'Mystery'],
            ['file_path' => '', 'name' => 'Citizen Kane', 'year' => 1941, 'main_actor' => 'Orson Welles', 'genre' => 'Drama'],
            ['file_path' => '', 'name' => 'Coco', 'year' => 2017, 'main_actor' => 'Anthony Gonzalez', 'genre' => 'Animation'],
            ['file_path' => '', 'name' => 'Creed', 'year' => 2015, 'main_actor' => 'Michael B. Jordan', 'genre' => 'Drama'],
            ['file_path' => '', 'name' => 'Dances with Wolves', 'year' => 1990, 'main_actor' => 'Kevin Costner', 'genre' => 'Adventure'],
            ['file_path' => '', 'name' => 'Die Hard', 'year' => 1988, 'main_actor' => 'Bruce Willis', 'genre' => 'Action'],
            ['file_path' => '', 'name' => 'Dirty Dancing', 'year' => 1987, 'main_actor' => 'Patrick Swayze', 'genre' => 'Romance'],
            ['file_path' => '', 'name' => 'Doctor Strange', 'year' => 2016, 'main_actor' => 'Benedict Cumberbatch', 'genre' => 'Action'],
            ['file_path' => '', 'name' => 'Donnie Darko', 'year' => 2001, 'main_actor' => 'Jake Gyllenhaal', 'genre' => 'Sci-Fi'],
            ['file_path' => '', 'name' => 'Dune', 'year' => 2021, 'main_actor' => 'Timothée Chalamet', 'genre' => 'Sci-Fi'],
            ['file_path' => '', 'name' => 'Dunkirk', 'year' => 2017, 'main_actor' => 'Fionn Whitehead', 'genre' => 'War'],
            ['file_path' => '', 'name' => 'E.T. the Extra-Terrestrial', 'year' => 1982, 'main_actor' => 'Henry Thomas', 'genre' => 'Sci-Fi'],
            ['file_path' => '', 'name' => 'Edward Scissorhands', 'year' => 1990, 'main_actor' => 'Johnny Depp', 'genre' => 'Fantasy'],
            ['file_path' => '', 'name' => 'Elf', 'year' => 2003, 'main_actor' => 'Will Ferrell', 'genre' => 'Comedy'],
            ['file_path' => '', 'name' => 'Elysium', 'year' => 2013, 'main_actor' => 'Matt Damon', 'genre' => 'Sci-Fi'],
            ['file_path' => '', 'name' => 'Eternal Sunshine of the Spotless Mind', 'year' => 2004, 'main_actor' => 'Jim Carrey', 'genre' => 'Romance'],
            ['file_path' => '', 'name' => 'Ex Machina', 'year' => 2014, 'main_actor' => 'Domhnall Gleeson', 'genre' => 'Sci-Fi'],
            ['file_path' => '', 'name' => 'Fargo', 'year' => 1996, 'main_actor' => 'Frances McDormand', 'genre' => 'Crime'],
            ['file_path' => '', 'name' => 'Ferris Bueller\'s Day Off', 'year' => 1986, 'main_actor' => 'Matthew Broderick', 'genre' => 'Comedy'],
            ['file_path' => '', 'name' => 'Fight Club', 'year' => 1999, 'main_actor' => 'Brad Pitt', 'genre' => 'Drama'],
            ['file_path' => '', 'name' => 'Finding Dory', 'year' => 2016, 'main_actor' => 'Ellen DeGeneres', 'genre' => 'Animation'],
            ['file_path' => '', 'name' => 'Footloose', 'year' => 1984, 'main_actor' => 'Kevin Bacon', 'genre' => 'Drama'],
            ['file_path' => '', 'name' => 'Freaky Friday', 'year' => 2003, 'main_actor' => 'Lindsay Lohan', 'genre' => 'Comedy'],
            ['file_path' => '', 'name' => 'Get Out', 'year' => 2017, 'main_actor' => 'Daniel Kaluuya', 'genre' => 'Horror'],
            ['file_path' => '', 'name' => 'Ghostbusters', 'year' => 1984, 'main_actor' => 'Bill Murray', 'genre' => 'Comedy'],
            ['file_path' => '', 'name' => 'Gladiator', 'year' => 2000, 'main_actor' => 'Russell Crowe', 'genre' => 'Action'],
            ['file_path' => '', 'name' => 'Gone Girl', 'year' => 2014, 'main_actor' => 'Ben Affleck', 'genre' => 'Mystery'],
            ['file_path' => '', 'name' => 'Gone with the Wind', 'year' => 1939, 'main_actor' => 'Clark Gable', 'genre' => 'Romance'],
            ['file_path' => '', 'name' => 'Good Will Hunting', 'year' => 1997, 'main_actor' => 'Matt Damon', 'genre' => 'Drama'],
            ['file_path' => '', 'name' => 'Goodfellas', 'year' => 1990, 'main_actor' => 'Robert De Niro', 'genre' => 'Crime'],
            ['file_path' => '', 'name' => 'Grease', 'year' => 1978, 'main_actor' => 'John Travolta', 'genre' => 'Musical'],
            ['file_path' => '', 'name' => 'Guardians of the Galaxy', 'year' => 2014, 'main_actor' => 'Chris Pratt', 'genre' => 'Action'],
            ['file_path' => '', 'name' => 'Hairspray', 'year' => 2007, 'main_actor' => 'Nikki Blonsky', 'genre' => 'Musical'],
            ['file_path' => '', 'name' => 'Halloween', 'year' => 1978, 'main_actor' => 'Jamie Lee Curtis', 'genre' => 'Horror'],
            ['file_path' => '', 'name' => 'Harry Potter and the Sorcerer\'s Stone', 'year' => 2001, 'main_actor' => 'Daniel Radcliffe', 'genre' => 'Fantasy'],
            ['file_path' => '', 'name' => 'Indiana Jones and the Last Crusade', 'year' => 1989, 'main_actor' => 'Harrison Ford', 'genre' => 'Adventure'],
            ['file_path' => '', 'name' => 'Inside Out', 'year' => 2015, 'main_actor' => 'Amy Poehler', 'genre' => 'Animation'],
            ['file_path' => '', 'name' => 'It\'s a Wonderful Life', 'year' => 1946, 'main_actor' => 'James Stewart', 'genre' => 'Drama'],
            ['file_path' => '', 'name' => 'Jerry Maguire', 'year' => 1996, 'main_actor' => 'Tom Cruise', 'genre' => 'Drama'],
            ['file_path' => '', 'name' => 'Joker', 'year' => 2019, 'main_actor' => 'Joaquin Phoenix', 'genre' => 'Crime'],
            ['file_path' => '', 'name' => 'Jumanji', 'year' => 1995, 'main_actor' => 'Robin Williams', 'genre' => 'Adventure'],
            ['file_path' => '', 'name' => 'Kill Bill', 'year' => 2003, 'main_actor' => 'Uma Thurman', 'genre' => 'Action'],
            ['file_path' => '', 'name' => 'La La Land', 'year' => 2016, 'main_actor' => 'Emma Stone', 'genre' => 'Musical'],
            ['file_path' => '', 'name' => 'Les Misérables', 'year' => 2012, 'main_actor' => 'Hugh Jackman', 'genre' => 'Musical'],
            ['file_path' => '', 'name' => 'Life of Pi', 'year' => 2012, 'main_actor' => 'Suraj Sharma', 'genre' => 'Adventure'],
            ['file_path' => '', 'name' => 'Lion King (1994)', 'year' => 1994, 'main_actor' => 'Matthew Broderick', 'genre' => 'Animation'],
            ['file_path' => '', 'name' => 'Little Miss Sunshine', 'year' => 2006, 'main_actor' => 'Abigail Breslin', 'genre' => 'Comedy'],
            ['file_path' => '', 'name' => 'Logan', 'year' => 2017, 'main_actor' => 'Hugh Jackman', 'genre' => 'Action'],
            ['file_path' => '', 'name' => 'Mad Max', 'year' => 2015, 'main_actor' => 'Tom Hardy', 'genre' => 'Action'],
            ['file_path' => '', 'name' => 'Mamma Mia!', 'year' => 2008, 'main_actor' => 'Meryl Streep', 'genre' => 'Musical'],
            ['file_path' => '', 'name' => 'Manchester by the Sea', 'year' => 2016, 'main_actor' => 'Casey Affleck', 'genre' => 'Drama'],
            ['file_path' => '', 'name' => 'Mean Girls', 'year' => 2004, 'main_actor' => 'Lindsay Lohan', 'genre' => 'Comedy'],
            ['file_path' => '', 'name' => 'Men in Black', 'year' => 1997, 'main_actor' => 'Will Smith', 'genre' => 'Sci-Fi'],
            ['file_path' => '', 'name' => 'Million Dollar Baby', 'year' => 2004, 'main_actor' => 'Hilary Swank', 'genre' => 'Drama'],
            ['file_path' => '', 'name' => 'Mission: Impossible', 'year' => 1996, 'main_actor' => 'Tom Cruise', 'genre' => 'Action'],
            ['file_path' => '', 'name' => 'Moana', 'year' => 2016, 'main_actor' => 'Auli\'i Cravalho', 'genre' => 'Animation'],
            ['file_path' => '', 'name' => 'Monsters, Inc.', 'year' => 2001, 'main_actor' => 'John Goodman', 'genre' => 'Animation'],
            ['file_path' => '', 'name' => 'Moonlight', 'year' => 2016, 'main_actor' => 'Mahershala Ali', 'genre' => 'Drama'],
            ['file_path' => '', 'name' => 'Moulin Rouge!', 'year' => 2001, 'main_actor' => 'Nicole Kidman', 'genre' => 'Musical'],
            ['file_path' => '', 'name' => 'Mrs. Doubtfire', 'year' => 1993, 'main_actor' => 'Robin Williams', 'genre' => 'Comedy'],
            ['file_path' => '', 'name' => 'Mulan', 'year' => 1998, 'main_actor' => 'Ming-Na Wen', 'genre' => 'Animation'],
            ['file_path' => '', 'name' => 'Night at the Museum', 'year' => 2006, 'main_actor' => 'Ben Stiller', 'genre' => 'Adventure'],
            ['file_path' => '', 'name' => 'No Country for Old Men', 'year' => 2007, 'main_actor' => 'Javier Bardem', 'genre' => 'Crime'],
            ['file_path' => '', 'name' => 'Ocean\'s Eleven', 'year' => 2001, 'main_actor' => 'George Clooney', 'genre' => 'Crime'],
            ['file_path' => '', 'name' => 'One Flew Over the Cuckoo\'s Nest', 'year' => 1975, 'main_actor' => 'Jack Nicholson', 'genre' => 'Drama'],
            ['file_path' => '', 'name' => 'Raiders of the Lost Ark', 'year' => 1981, 'main_actor' => 'Harrison Ford', 'genre' => 'Adventure'],
            ['file_path' => '', 'name' => 'Ratatouille', 'year' => 2007, 'main_actor' => 'Patton Oswalt', 'genre' => 'Animation'],
            ['file_path' => '', 'name' => 'Requiem for a Dream', 'year' => 2000, 'main_actor' => 'Jared Leto', 'genre' => 'Drama'],
            ['file_path' => '', 'name' => 'Rocky', 'year' => 1976, 'main_actor' => 'Sylvester Stallone', 'genre' => 'Drama'],
            ['file_path' => '', 'name' => 'Saving Private Ryan', 'year' => 1998, 'main_actor' => 'Tom Hanks', 'genre' => 'War'],
            ['file_path' => '', 'name' => 'Scarface', 'year' => 1983, 'main_actor' => 'Al Pacino', 'genre' => 'Crime'],
            ['file_path' => '', 'name' => 'Schindler\'s List', 'year' => 1993, 'main_actor' => 'Liam Neeson', 'genre' => 'Biography'],
            ['file_path' => '', 'name' => 'Silence of the Lambs', 'year' => 1991, 'main_actor' => 'Jodie Foster', 'genre' => 'Thriller'],
            ['file_path' => '', 'name' => 'Singin\' in the Rain', 'year' => 1952, 'main_actor' => 'Gene Kelly', 'genre' => 'Musical'],
            ['file_path' => '', 'name' => 'Skyfall', 'year' => 2012, 'main_actor' => 'Daniel Craig', 'genre' => 'Action'],
            ['file_path' => '', 'name' => 'Slumdog Millionaire', 'year' => 2008, 'main_actor' => 'Dev Patel', 'genre' => 'Drama'],
            ['file_path' => '', 'name' => 'Snow White and the Seven Dwarfs', 'year' => 1937, 'main_actor' => 'Adriana Caselotti', 'genre' => 'Animation'],
            ['file_path' => '', 'name' => 'Star Wars: Episode IV - A New Hope', 'year' => 1977, 'main_actor' => 'Mark Hamill', 'genre' => 'Sci-Fi'],
            ['file_path' => '', 'name' => 'Taxi Driver', 'year' => 1976, 'main_actor' => 'Robert De Niro', 'genre' => 'Crime'],
            ['file_path' => '', 'name' => 'Terminator 2: Judgment Day', 'year' => 1991, 'main_actor' => 'Arnold Schwarzenegger', 'genre' => 'Sci-Fi'],
            ['file_path' => '', 'name' => 'The Exorcist', 'year' => 1973, 'main_actor' => 'Linda Blair', 'genre' => 'Horror'],
            ['file_path' => '', 'name' => 'The Grand Budapest Hotel', 'year' => 2014, 'main_actor' => 'Ralph Fiennes', 'genre' => 'Comedy'],
            ['file_path' => '', 'name' => 'The Greatest Showman', 'year' => 2017, 'main_actor' => 'Hugh Jackman', 'genre' => 'Musical'],
            ['file_path' => '', 'name' => 'The Notebook', 'year' => 2004, 'main_actor' => 'Ryan Gosling', 'genre' => 'Romance'],
            ['file_path' => '', 'name' => 'The Princess Bride', 'year' => 1987, 'main_actor' => 'Cary Elwes', 'genre' => 'Fantasy'],
            ['file_path' => '', 'name' => 'The Sound of Music', 'year' => 1965, 'main_actor' => 'Julie Andrews', 'genre' => 'Musical'],
            ['file_path' => '', 'name' => 'The Wizard of Oz', 'year' => 1939, 'main_actor' => 'Judy Garland', 'genre' => 'Fantasy'],
            ['file_path' => '', 'name' => 'To Kill a Mockingbird', 'year' => 1962, 'main_actor' => 'Gregory Peck', 'genre' => 'Drama'],
            ['file_path' => '', 'name' => 'Up', 'year' => 2009, 'main_actor' => 'Edward Asner', 'genre' => 'Animation'],
            ['file_path' => '', 'name' => 'West Side Story', 'year' => 1961, 'main_actor' => 'Natalie Wood', 'genre' => 'Musical'],
            ['file_path' => '', 'name' => 'Whiplash', 'year' => 2014, 'main_actor' => 'Miles Teller', 'genre' => 'Drama'],
            ['file_path' => '', 'name' => 'Willy Wonka & the Chocolate Factory', 'year' => 1971, 'main_actor' => 'Gene Wilder', 'genre' => 'Fantasy'],
            ['file_path' => '', 'name' => 'Wonder Woman', 'year' => 2017, 'main_actor' => 'Gal Gadot', 'genre' => 'Action'],
            ['file_path' => '', 'name' => 'Zootopia', 'year' => 2016, 'main_actor' => 'Ginnifer Goodwin', 'genre' => 'Animation'],
        ];

        foreach ($movies as $movie) {
            $movie['file_path'] = Helpers::getFilePath(Helpers::toSnakeCase($movie['name']));

            DB::table('files')->insert([
                'name' => $movie['name'],
                'year' => $movie['year'],
                'file_path' => $movie['file_path'],
                'main_actor' => $movie['main_actor'],
                'genre' => $movie['genre'],
                'type' => 'movie',
                'played_at' => null,
            ]);
        }
    }
}


