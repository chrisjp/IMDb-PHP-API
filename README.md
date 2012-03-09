# IMDb PHP API

## How To Use

### Create an instance

    // IMDb ( [bool $anonymise = false [, bool $summary = true [, int $titlesLimit = 0]]] )
    $imdb = new IMDb(true, true, 0);	// anonymise requests to prevent IP address getting banned, summarise returned data, unlimited films returned


### Search for a movie by title

    // Returns an array containing objects of matching titles
    $movies = $imdb->find_by_title("The Godfather"); 
    
    // $movies[0]->title => "The Godfather"
    // $movies[0]->year => "1972"
    // $movies[0]->tconst => "tt0068646"
    // ... 
    // $movies[1]->title => "The Godfather: Part II"
    // ...

### Get a movie by its imdb_id

    // Returns an object containing the movie's data
    $movie = $imdb->find_by_id("tt0068646");

    // $movie->title => "The Godfather"
    // $movie->rating => "8.1"
    // $movie->year => "1972"

### Search for a person by name

    // Returns an array containing objects of matching names
    $people = $imdb->person_by_name("Christian Bale"); 
    
    // $people[0]->name => "Christian Bale"
    // $people[0]->nconst => "nm0000288"
    // ... 

### Get a person by their imdb_id

    // Returns an object containing the person's data
    $person = $imdb->person_by_id("nm0000288");

    // $person->name => "Christian Bale"
    // $person->birthday_datestamp => "1974-01-30"
    // $person->birthplace => "Haverfordwest, Pembrokeshire, Wales, UK"
    
### Charts

    // Returns an array containing objects of of the top 250 films
    $movies = $imdb->chart_top();
    // Or for the Bottom 100...
    $movies = $imdb->chart_bottom();
    
    // $movies[0]->title => "The Shawshank Redemption"
    // $movies[0]->year => "1994"
    // $movies[0]->tconst => "tt0111161"
    // ... 
    // $movies[1]->title => "The Godfather"
    // ...
    
    // Similarly, the following function will return the Box Office (US) respectively.
    // Note that the format of the data returned differs slightly from the above 2 charts
    $movies = $imdb->boxoffice();
    