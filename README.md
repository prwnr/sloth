
# Sloth

[Sloth](https://sloth.semicket.com) is a time tracking web application, it comes with few additional features, that makes it a bit different than all the time tracking applications out there.

The main concept of this application is to provide a not too complex way to track work time of your projects, clients and team members.
In addition to that 'simple' concept, there are few more things, such as:  multiple teams, reports, todo list, budget management, charts and more. 

This application is a 'home project' and is made to help with my own time management with the thought that someone else may use it. I'm also improving my programming skills while working on it.
## Table of Contents

1. [Technology and requirements](#technology-and-requirements)
2. [Installation](#installation)
3. [Sloth features](#sloth-features)
    - [Clients](#clients)
    - [Projects](#projects)
    - [Team members](#team-members)
    - [Time tracking](#time-tracking)
    - [Roles and permissions](#roles-and-permissions)
    - [Todo tasks](#todo-tasks)
    - [Detailed reports](#detailed-reports)
4. [TODO](#todo)

## Technology and requirements

_Sloth_ application is built with:
- **Laravel 5.7**
- **Vue.js 2.5** with **Vue Router 3.0**
    
Requirements:
- PHP 7.1+
- database compatible with MySQL
- NodeJS and NPM (it's suggested that NPM should be at least 5.6+)

## Installation

If you would like to install _Sloth_ on your server, there are few small steps to do it:
1. Project installation
    * run `composer create-project prwnr/sloth` command to get project files and install all packages,
    * or clone repository and run `composer install` command to install packages
2. Install NPM packages and run `npm run prod` command to compile JS and CSS files
3. Run `php artisan migrate` command to import application tables
4. Run `php artisan sloth:install` command to install base permissions and currencies used by _Sloth_

Optional step: run `php artisan db:seed` command to seed database with testing user: `test@test.com / secret`

## Sloth features

When creating an account in _Sloth_, you are creating a team owner, that can create his
clients, projects and members to manage their time and salary. 

What _Sloth_ is exactly capable of? See below:

#### Clients

This place is where you can create your own clients with all their information and billing details,
such as currency and a rate (hourly/fixed). Clients are always associated with projects, so a time logged
to a project of your particual client will be also shown for that client. 

<img src="https://semicket.com/storage/app/media/sloth/client.png">

#### Projects

This part of the application is where you can create your projects, where each project is assigned to its own client and 
lets you assign what members should be capable of logging time for it.
Projects can inherit their clients currency and billings, but can also override them if there is a need to do that. 
On top of that projects can have their tasks (few predefined that don't have to be used) with their own billings and option to set them
as not billable. The last projects feature is a budget - which lets you set a threshold for the project time logged based on the billings.

<img src="https://semicket.com/storage/app/media/sloth/project.png"> 

#### Team members

Members are one of the main concepts of _Sloth_ application, providing non-limited assigment to multiple teams.
Each member is created with his own billing, roles and projects. When member is created for the first time,
his account is created as well with a password sent to his email. With any new attempt on creating a member 
for the same email address - new member associated to the same, single account is created, allowing that person
to switch between his teams once logged in. Teams are separated and are not interfering with others.

<img src="https://semicket.com/storage/app/media/sloth/member.png"> 

#### Time tracking

Time tracking is the other main _Sloth_ concept. It allows each member to log his work time for current, past and future days.
This comes with few options, such as choosing a project for which time should be logged, a task (if required) and a description of the log. 
Each log of the current day can be started and stopped at will and also its time edited manually. However past logs cannot be started, but can be edited (and that's only with proper permission).
In addition to that, logs information (project, task and description) can be edited at any time and its date changed to different.  

<img src="https://semicket.com/storage/app/media/sloth/tracker.png"> 

#### Roles and permissions

_Sloth_ comes with permissions for main application features, such as: time tracking, team, roles, projects and clients management, viewing detailed reports and editing time.

When creating team owner account, base 3 roles are created: administrator, manager and programmer. 
Administrator role is assigned to the team owner and it cannot be deleted nor edited. Manager role on the other hand
can't be only deleted. Third role is an example role and can be deleted. 
Application permissions are immutable - cannot be edited and deleted. 
However every member that can manage roles, can create new roles and assig any permissions to them as he wants to.

#### Todo tasks

Todo tasks list is an additional feature in _Sloth_ that provides a capabilities to track not only your own time, but also 
a list of things that should be done (in work or home). This list allows you to choose a project to which it should be associated
and a priority. Priorties are ordered on the list from high, to medium and low. Each todo task can be marked as done, removed or edited.

<img src="https://semicket.com/storage/app/media/sloth/todo.png"> 

#### Detailed reports

Reports page is a place where all team logs are being displayed with a bit more information about them.
Reports list can be filtered by members, clients, projects, status and billable state. All logs can be filtered by date (this week, this month, this year or a custom date).
The table shows all important information about each log, such as a member, client and project names with date, logged hours and status (finished, in progress). In addition to that, 
details can be showed for each log. This allows a viewer to check how much member earned on that log, how much team owner earned on the project and what is client salary.

<img src="https://semicket.com/storage/app/media/sloth/reports.png"> 

## TODO

This application is still a 'work in progress', so there's a lot to add and there are things to fix.

List of bigger features that will be added in future: 

- generating PDF/XLS report files from report view
- more detailed permissions for member roles
~~- starting a work log from within a todo task~~
- creating multiple teams for a single administrator
- messages communication between team members
- messages notifications for various actions in application

Im also open for any suggestions for new features that _Sloth_ could have.
