document.addEventListener("DOMContentLoaded", function () {
    const steps = [
        "Login to Your Dashboard.",
        "Check Outstanding Balance.",
        "Make Payments Securely.",
        "Download Your Clearance Form Instantly."
    ];

    const howItWorksList = document.getElementById("howItWorksList");

    steps.forEach(step => {
        const listItem = document.createElement("li");
        listItem.textContent = step;
        listItem.classList.add("list-group-item");
        howItWorksList.appendChild(listItem);
    });
});
