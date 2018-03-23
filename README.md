# logs.tf log combiner API
* Submit POST data to http://logs.sharky.tf/api/post
* ALL of the following fields must be used:
  * `upload[]` : the files to upload
  * `title` : the title you want your log to have
  * `map` : the map that the logs took place in
  * `api` : your [logs.tf api key](http://logs.tf/about)
* There is also an option to do the same thing using the GET method @ http://logs.sharky.tf/api/get
