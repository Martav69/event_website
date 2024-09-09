# SportHub 
## Overview 
SportHub is a sports event website I developed using Symfony 6.4 with PHP 8.3. I used Tailwind CSS for the front-end and Twig templates for rendering. This platform is designed to allow users to create, search, and manage sports events. Whether you're a sports club, a private coach, or just looking for local events, SportHub provides a user-friendly interface to do so.
## Features 
- **User Registration & Authentication**: Users can register, log in, and create their events. 
- **Event Creation & Management**: Users can add details, upload images, and manage registrations for their events. 
-  **Search and Filters**: Search by event title, city, category, or description. Filters are available for category and price range. 
- **Stripe Integration**: Allows users to pay for events through a Stripe checkout page. 
- **Pagination**: Events are paginated for ease of navigation using KnpPaginatorBundle. 
- **Dynamic Form Management**: The city selector checks if the city exists for a country, and if not, it creates a new entry.
  

## Installation

  

1 - Clone The Repository

  

```bash

git clone https://github.com/Martav69/event_website.git

```

2 - Install dependencies

  

```bash

composer install

```

3 - Create the `.env.local\` file and configure your database connection:

```bash

DATABASE_URL="mysql://username:password@127.0.0.1:3306/sporthub"

```

>  **Note**

> Replace username, password, and sporthub with your actual database credentials and database name.

  

4 - Set up the database:

  
  

```bash

php bin/console doctrine:database:create

php bin/console doctrine:migrations:migrate

php bin/console doctrine:fixtures:load

```

5 - Run The Server ( don't forget tailwind )

```bash

symfony serve --no-tls

php bin/console tailwind:build --watch 

```


## Usage

### Event Creation
Users can create events by filling in the form, which dynamically handles city and country relationships. Below is an example of the event creation page:

### Event List and Search
Once logged in, users can search events by various parameters such as category, city, or event title. Filters are available to narrow down the search:

### Stripe Payment
The application integrates with Stripe for secure payments. Users are redirected to a checkout page to complete their purchase for paid events.

## Files To See 

### EventController 
With the request and the manipulation and creation of all the filter 
The handling of the country and city with the link between them depending if it already exist or not 

### PaymentController 
Dependency injection to have a price that update himself in link with the event price 

### AppFixtures
Using different Method to populate the databases
With a populate of the country with the help of a JSON File using the umpirsky package 

### Form/EventType 
Manipulation of the city and the country, to manipulate them as i wanted. 
So it's unmapped to manipulate them after in the event controller. 



## Challenges Faced

> [!IMPORTANT]
> **City-Country Relationship**: One of the trickier parts of the project was managing the relationship between cities and countries. Events have a many-to-many relation with cities, and each city is related to a single country. I wanted the system to create a new city automatically if it didn’t exist already for the given country. This required custom logic in the form handling, but it was an interesting problem to solve.

> [!NOTE]
> **Search and Filters**: I implemented search functionality across multiple fields, including title, category, city, and event description. The search form is located in the navbar, and additional filters, like the price range slider, are located in a sidebar. One issue I encountered was the disappearing price range slider after every search, but I managed to work around it by splitting the JavaScript for the slider into its own file and dynamically fetching the min and max price values from the URL.

> [!TIP]
> **Pagination Bug**: Another challenge involved pagination using KnpPaginatorBundle. Initially, sorting by price, date, or alphabetical order wasn’t functioning as expected. I solved this by creating a `knp_paginator.yaml` file and disabling certain sort options that conflicted with my custom queries.

## Improvements and Refactoring

> [!WARNING]
> **Controller Refactoring**: Currently, my `EventController` handles a lot of logic. In the future, I plan to refactor the controller by moving some logic to services. This will make the project more scalable and maintainable.

> [!NOTE]
> **Order By ASC/DESC Bug**: While I fixed most of the issues with pagination and sorting, I encountered a bug related to sorting by ascending or descending order. It was caused by the paginator bundle's sort field name options. I disabled the unnecessary options to get the correct sorting behavior.

## Future Features

- **Event Tags**: I plan to add a tagging system for events, so users can search by multiple keywords.
- **Event Reviews**: Another idea is to allow users to leave reviews for events they've attended.
- **Event Notifications**: I’m also considering adding notifications for users when new events are created in their selected categories or cities.

## Conclusion
Working on this project has been a learning experience, especially in handling database relations, pagination, and search functionalities. While there are still areas to improve, I’m happy with how the project turned out and look forward to further refining it.

