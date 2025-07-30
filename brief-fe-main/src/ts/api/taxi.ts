// import { ApiResponse, Booking} from "../types"
// import { myFetch } from "../utils/my-fetch.ts"
// const API_BASE_URL = import.meta.env.VITE_API_BASE_URL

// export function fetchAllBookings(
//   callback: (bookings: Booking[]) => void,
// ): void {
//   fetch(`${API_BASE_URL}/bookings/`)
//     .then((response) => {
//       if (!response.ok) {
//         throw new Error("Netwerk response was niet ok")
//       }
//       return response.json() as Promise<ApiResponse>
//     })
//     .then((data: ApiResponse) => {
//       if (!data.bookings) {
//         console.error("Geen boekingen ontvangen")
//         return
//       }
//       const bookings = data.bookings
//       bookings.sort(
//         (a, b) =>
//           new Date(a.datetime).getTime() - new Date(b.datetime).getTime(),
//       )
//       callback(bookings)
//       console.log(bookings)
//     })
//     .catch((error) => {
//       console.error("Er is een fout opgetreden:", error)
//     })
// }

// export function getRitByID(
//   id: string,
//   callback: (bookings: Booking[]) => void,
// ): void {
//   fetch(`${API_BASE_URL}/bookings/${id}`)
//     .then((response) => {
//       if (!response.ok) {
//         throw new Error("Netwerk response was niet ok")
//       }
//       return response.json() as Promise<ApiResponse>
//     })
//     .then((data: ApiResponse) => {
//       if (!data.bookings) {
//         console.error("Geen boekingen ontvangen")
//         return
//       }
//       const bookings = data.bookings
//       bookings.sort(
//         (a, b) =>
//           new Date(a.datetime).getTime() - new Date(b.datetime).getTime(),
//       )
//       callback(bookings)
//       console.log(bookings)
//     })
//     .catch((error) => {
//       console.error("Er is een fout opgetreden:", error)
//     })
// }

// export function fetchAllBookingsFromUser(
//   callback: (bookings: Booking[]) => void,
// ): void {
//   myFetch(`${API_BASE_URL}/users/bookings`)
//     .then((response) => {
//       if (!response.ok) {
//         throw new Error("Netwerk response was niet ok")
//       }
//       return response.json() as Promise<ApiResponse>
//     })
//     .then((data: ApiResponse) => {
//       if (!data.bookings) {
//         console.error("Geen boekingen ontvangen")
//         return
//       }
//       const bookings = data.bookings
//       bookings.sort(
//         (a, b) =>
//           new Date(a.datetime).getTime() - new Date(b.datetime).getTime(),
//       )
//       callback(bookings)
//       console.log(bookings)
//     })
//     .catch((error) => {
//       console.error("Er is een fout opgetreden:", error)
//     })
// }



