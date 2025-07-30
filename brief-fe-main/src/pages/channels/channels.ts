import { fetchChannels } from "../../ts/api/channels";

export function renderAllChannels(): void {
  const container = document.getElementById("channels");
  if (!container) return;

  fetchChannels((channels) => {
    container.innerHTML = ""; // Leegmaken

    channels.forEach((channel) => {
      const li = document.createElement("li");
      li.className = "channel-item";

      li.innerHTML = `
        <div class="channel-card">
          <img src="../../img/channel1.jpg" alt="${channel.name} afbeelding" />
          <div class="channel-info">
            <h2>${channel.name}</h2>
          </div>
          <div class="channel-actions">
            <a href="detail.html?id=${channel.id}" class="view-posts-btn">Bekijk posts</a>
            <label class="subscribe-checkbox">
              <input type="checkbox" name="subscribe" /> Abonneren
            </label>
          </div>
        </div>
      `;

      container.appendChild(li);
    });

    console.log("Kanalen succesvol geladen:", channels);
  });
}

document.addEventListener("DOMContentLoaded", () => {
  renderAllChannels();
});

// import { fetchChannels } from "../../ts/api/channels"; // zonder .ts extensie

// document.addEventListener("DOMContentLoaded", () => {
//   fetchChannels((channels) => {
//     const container = document.getElementById("channels");
//     if (!container) return;

//     channels.forEach((channel) => {
//       const li = document.createElement("li");
//       li.className = "channel-item";

//       li.innerHTML = `
//         <div class="channel-card">
//           <img src="../../img/channel1.jpg" alt="${channel.name} afbeelding" />
//           <div class="channel-info">
//             <h2>${channel.name}</h2>
//           </div>
//           <div class="channel-actions">
//             <a href="detail.html?id=${channel.id}" class="view-posts-btn">Bekijk posts</a>
//             <label class="subscribe-checkbox">
//               <input type="checkbox" name="subscribe" /> Abonneren
//             </label>
//           </div>
//         </div>
//       `;

//       container.appendChild(li);
//     });

//     console.log("Kanalen succesvol geladen:", channels);
//   });
// });
