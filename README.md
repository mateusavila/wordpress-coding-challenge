

# wordpress-coding-challenge - Hubstaff Front-end Challenge (WP Rest API)

### About the files
  
This test will have 2 repositories to work: the WP Rest API repository and the front-end repository. This documents talks only the API routes.

This repository has the minimum list of necessary files, just to run this test. We have here:

- Metabox with the possibility to define **featured post**
- Custom checkbox just to client decide which post is featured or not
- Two custom API routes, just to consume the data from Wordpress.

### About the routes
If you want to see how the data flows from the Wordpress API, you can look where the API is hosted (https://wp.mateusavila.com.br/clientes/hubstaff/wp-json).

This api has two custom-made routes:
- **/api/home**: this routes shows 3 arrays of posts, divided by *sticky*, *featured* and *newest* posts. 
- **/api/blog**: this route brings the pagination for each newest posts. This route demands the  `?page=[number]`to bring the correct pagination data.

Every route has an array with all this fields:
#### Base route
- data
	- sticky: Array
		- Base WP data
	- featured: Array
		- Base WP data
	- newest: Array
		- Base WP data

#### Base WP data
| key | value |
|--|--|
| id | number |
| title | string |
| content | string |
| excerpt | string |
| category | CategoryWP Scheme |
| slug | string |
| thumbnail | string |
| width | number |
| height | number |
| views | number |
| date | string (format F j, Y) |
| updated_date | string (format F j, Y) |
| author | AuthorWP Scheme |

#### CategoryWP Scheme
| key | value |
|--|--|
| id | number |
| name | string |
| slug | string |
| term_group | number |
| term_taxonomy_id | number |
| taxonomy | string |
| description | string |
| parent | number |
| count | number |
| filter | string |
| cat_ID | number |
| category_count | number |
| category_description | string |
| cat_name | string |
| category_nicename | string |
| category_parent | number |

#### AuthorWP Scheme
| key | value |
|--|--|
| name | string |
| photo | string |
| first_name | string |
| last_name | string |
| email | string |
