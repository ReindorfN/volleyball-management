


document.addEventListener("DOMContentLoaded", () => {
    // Fetch data from backend
    fetch("../actions/fetch_users.php")
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error(data.error);
                return;
            }

            // Populate tables based on roles
            populateTables(data);
        })
        .catch(error => {
            console.error("Error fetching user data:", error);
        });

    function populateTables(users) {
        // Populate Coaches Table
        const coachesTable = document.querySelector("#coaches_table tbody");
        const coachUsers = users.filter(user => user.role === "coach");
        coachUsers.forEach(user => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${user.username}</td>
                <td>${user.email}</td>
                <td>${user.team_name || "N/A"}</td>
                <td>
                    <button>Edit</button>
                    <button>Delete</button>
                </td>
            `;
            coachesTable.appendChild(row);
        });

        // Populate Organizers Table
        const organizersTable = document.querySelector("#organizers_table tbody");
        const organizerUsers = users.filter(user => user.role === "organizer");
        organizerUsers.forEach(user => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${user.username}</td>
                <td>${user.email}</td>
                <td>
                    <button>Edit</button>
                    <button>Delete</button>
                </td>
            `;
            organizersTable.appendChild(row);
        });

        // Populate Fans Table
        const fansTable = document.querySelector("#fans_table tbody");
        const fanUsers = users.filter(user => user.role === "fan");
        fanUsers.forEach(user => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${user.first_name}</td>
                <td>${user.last_name}</td>
                <td>${user.email}</td>
                <td>
                    <button>Delete</button>
                </td>
            `;
            fansTable.appendChild(row);
        });
    }

// Function to connect navication buttons to respective panels
    function showPanel(panelId, buttonId){
        // Hiding all panels
        document.getElementById('rightpanel-dashboard').classList.remove('active');
        document.getElementById('rightpanel-coaches').classList.remove('active');
        document.getElementById('rightpanel-organizers').classList.remove('active');
        document.getElementById('rightpanel-players').classList.remove('active');
    
        // disabling active button
        document.getElementById('dashboard_btn').classList.remove('active-btn');
        document.getElementById('coaches_btn').classList.remove('active-btn');
        document.getElementById('organizers_btn').classList.remove('active-btn');
        document.getElementById('players_btn').classList.remove('active-btn');
    
        // Dislaying selectd button and panel
        document.getElementById(panelId).classList.add('active');
        document.getElementById(buttonId).classList.add('active_btn');
    }


    // function implemntation/ call
    document.getElementById('dashboard_btn').addEventListener('click', function(){
        showPanel('rightpanel-dashboard', 'dashboard_btn');
    });

    document.getElementById('coaches_btn').addEventListener('click', function(){
        showPanel('rightpanel-coaches', 'coaches_btn');
    });

    document.getElementById('organizers_btn').addEventListener('click', function(){
        showPanel('rightpanel-organizers', 'organizers_btn');
    });

    document.getElementById('players_btn').addEventListener('click', function(){
        showPanel('rightpanel-players', 'players_btn');
    });


});
