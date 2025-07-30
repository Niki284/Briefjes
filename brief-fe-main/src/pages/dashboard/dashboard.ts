// import { Booking } from "../../ts/types/index.ts"
// import { hasRole} from "../../ts/utils/auth.ts"
// import { fetchAllBookingsFromUser} from "../../ts/api/taxi.ts"

// if (!hasRole("user")) {
//   location.assign(`../401/?from=${window.location.href}`)
// }

// let bookings: Booking[] = [];

// function renderBookings(): void {
//   const tbody = document.getElementById('requests-tbody');
//   if (tbody) {
//     tbody.innerHTML = '';
//     bookings.forEach(booking => {
//       const row = document.createElement('tr');

//       const destinationCell = document.createElement('td');
//       destinationCell.textContent = booking.pickup;
//       row.appendChild(destinationCell);

//       const priceCell = document.createElement('td');
//       priceCell.textContent = booking.dropoff;
//       row.appendChild(priceCell);

//       const timeCell = document.createElement('td');
//       timeCell.textContent = new Date(booking.datetime).toLocaleString('nl-BE', { day: '2-digit', month: '2-digit', year: 'numeric' });
//       row.appendChild(timeCell);

//       tbody.appendChild(row);
//     });
//   }
// }


// fetchAllBookingsFromUser((fetchedBookings: Booking[]) => {
//   bookings = fetchedBookings;
//   renderBookings();
// });