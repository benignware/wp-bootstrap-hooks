document.addEventListener("click", function (event) {
  // Find the closest expandable input group
  const expandableGroup = event.target.closest(".input-group.is-expandable");

  if (!expandableGroup) {
    return; // Exit if not within an expandable group
  }

  // Find the search input and form
  const searchInput = expandableGroup.querySelector('input[type="search"]');
  const searchForm = expandableGroup.closest("form");

  if (!searchInput || !searchForm) {
    return; // Exit if elements are missing
  }

  // Ensure the input-group remains focused when the button is clicked
  const clickedButton = event.target.matches("button");
  if (clickedButton) {
    expandableGroup.focus();
  }

  // Prevent form submission if no search term is entered
  const hasSearchTerm = searchInput.value.trim().length > 0;
  if (!hasSearchTerm) {
    event.preventDefault();
    searchInput.focus();
  }
});

// Make the input-group focusable on page load
document.querySelectorAll(".input-group.is-expandable").forEach((group) => {
  group.setAttribute("tabindex", "0");
});
