# Change Log

## v0.4.1 - June 22, 2012
* Fixed: build_url() now generates a signature to sign the request, this fixes find_by_title() returning an empty result set.
* Known Issue: Anonymising find_by_title() requests will always return an empty result set. As of writing this, the only solution is simply to set $anonymise to false.

## v0.4 - March 9, 2012
* Added: Can now get top charts data including the Top 250, Bottom 100, and Box Office (US)

## v0.3 - March 8, 2012
* Added: Can now search for people by name (or a person with their IMDb ID) (test files included)
* Added: Film tagline now returned.
* Added: Arrays of genres, writers, directors and actors are now returned in addition to a comma-separated list of them.
* Fixed: PHP warning when film has no genres.
* Fixed: Adult genre filter now checks all genres instead of only the first one listed.

## v0.2.3 - December 11, 2011
* Added runtime (in minutes) to returned data from find_by_id().

## v0.2.2 - December 11, 2011
* find_by_title() now accepts optional 'year' parameter to limit results to titles from a given year

## v0.2.1 - December 11, 2011
* Option to ignore certain types of film, by default, tv_series, tv_special and video_game types are ignored
* Option to ignore Adult genre or X rated films - this is turned on by default
* Option to override above settings and force a title to be returned with find_by_id()

## v0.2.0 - December 11, 2011
* Summarised data now returned by default (as opposed to literally all information, which is not needed by most people)
* Basic error handling - "response" key added to summarised data, 0 indicates a failure while 1 indicates success.
* Stopped using American spellings for function and variable names
* Can now limit number of titles returned when using find_by_title() (NOTE: IMDb will only return a maximum of 50 results)

## v0.1.0 - November 24, 2011
* **Initial release**
* Return movie data from IMDb ID
* Return movies matching title
* Anonymise requests to prevent IP address getting banned
