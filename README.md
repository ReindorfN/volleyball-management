# 🏐 Ashesi Volleyball Management System
A web-based platform designed to manage and streamline volleyball activities for a targeted university. This system provides a centralized interface for players, coaches, organizers, administrators, and fans to interact with the sport in an organized, efficient, and real-time manner.

The platform aims to digitize the coordination of volleyball events, offering tools for team management, match scheduling, tournament organization, real-time engagement, and role-specific access control.


## 🔑 Key Roles & Access Levels

| _**Admin**_                                          | _**Coach**_                              | _**Organizer**_
| ---------------------------------------------------- | ---------------------------------------- | ------------------------------------
| - Full access to the system.                         | - Manages their team.                    | - Creates and manages tournaments.
| - Can add coaches and organizers.                    | - Adds players.                          | - Schedules matches.
| - Manages users, teams, and high-level system data.  | - Sets match strategies.                 | - Oversees event logistics.
|                                                      |  - Posts announcements to their team.    |
|                                                      |
| _**Player**_                                         | _**Fan**_
| - Views their team’s schedule and roster.            | - Views upcoming matches.
| - Accesses match strategies and team announcements.  | - Browses team and match info.
|                                                      | - Reads public announcements.



## ⚙️ Major Functionalities
_*✅ User Authentication:*_ 
* Role-based login for admins, coaches, organizers, players, and fans.
* Fans can sign up; all others are added by admins or coaches.

_*✅ Team Management*_
* Admins create teams and assign coaches.
+ Coaches manage their team's roster (add players via a form).

_*✅ Match Scheduling*_
* Organizers can schedule matches between teams.
* Matches include date, venue, and team assignments.

_*✅ Tournament Management*_
* Organizers can create and manage tournaments.
* Matches can be associated with tournaments.

_*✅ Match Strategy (Coach)*_
* Coaches can set strategies for upcoming matches.
* Players can view strategies assigned to their matches.

_*✅ Announcements*_
* Coaches can post team-specific announcements.
* Fans and players view announcements relevant to them.

_*✅ Real-Time Match Overview*_
* Fans and players can view a schedule of upcoming matches.
* Displays team names, venue, and time.


## 📊 Tech Stack
| Layer          | Technology            |
| -------------- | --------------------- |
| Frontend       | HTML, CSS, JavaScript |
| Backend        | PHP                   |
| Database       | MySQL / MariaDB       |
| Authentication | PHP Sessions          |


## 📌 Future Enhancements
- ++ Email notifications or SMS for upcoming matches.
- ++ Upload player photos and statistics.
- ++ Live match score updates.
- ++ Public leaderboard or standings view.

_NB: View [Video Demonstration](https://drive.google.com/file/d/1XZkvDPqO1P8I_6rOZfPis0sNQELEtVx6/view?usp=drive_link) of project here_
