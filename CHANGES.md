# Change Log

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
