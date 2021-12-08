# wordpress-coding-challenge

### Setting Up Your Enviornment

- Create a new instance of Wordpress.
- Using the Wordpress import tool, import the included [import.xml](import.xml) file, which will populate your database with ~300 posts.

### Project Requirements
- Create a child theme to the default WP theme
- Your task is to create a well-structured homepage tempalte similar to what we currently have on https://blog.hubstaff.com:
  - One sticky post at the top
    - The sticky post would take the full width of the container on the page, on all screen sizes.
  - Using the `add_post_meta` add the ability to feature any post from the wordpress admin. All featured posts will be listed below the sticky post. If a featured post is also a "sticky post", it will only show up as sticky at the top of the page.
    - The featured posts will be aligned in a grid of 4 articles per row on desktop, 3 articles per row on tablet, and one article per row on mobile.
  - Below we will have a list of 4 most recent articles (excluding sticky and featured posts)
    - These 4 articles will also be considered as the first page of the pagination. And clicking on a "See more recent articles" link would bring you to the second page showing posts from the 5th most recent to last, paginated into 10 articles per page. 
    - The recent posts will be aligned as two per row on tablet and wider screens and one per row on mobile.

We don't need the page to look fancy, just inheriting the parent theme styles. 

For the responsive styles you are not allowed to rely on any framework to help you with laying out the grid.


### Project Submission
- Fork this repo and submit a PR with only your child theme folder

### Submission
Please clone the repository and create a private repository on your own account. Then, create a new branch and submit a Pull Request with your proposed solution. Make sure to add and request review on the PR of the following github users:
- @miguelcdpmarques
- @stafie


### Evaluation Criteria
We'll be looking at the following criteria when assessing candidate submissions:
- Code simplicity and clarity
- Git history, including comments in the PR

**Good luck!**
