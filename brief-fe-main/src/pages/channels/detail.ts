import { fetchChannelById } from "../../ts/api/channels";

document.addEventListener("DOMContentLoaded", () => {
  const params = new URLSearchParams(window.location.search);
  const channelId = params.get("id");
  if (!channelId) return;

  const header = document.querySelector(".channel-header h1") as HTMLElement;
  const postsContainer = document.querySelector(".post-list") as HTMLElement;

  // 🔹 Haal kanaal + posts op
  fetchChannelById(channelId, ({ channel, posts }) => {
    if (!channel) {
      if (header) header.textContent = "Kanaal niet gevonden of geen toegang";
      return;
    }

    // Toon kanaalnaam
    header.textContent = channel.name;

    // Debug alle data
    console.log("Kanaal data ontvangen:", channel);
    console.log("Posts ontvangen:", posts);

    // 🔹 Render posts
    postsContainer.innerHTML = "";
    posts.forEach((post) => {
      const article = document.createElement("article");
      article.className = "post-item";

      article.innerHTML = `
        <h2>${post.title}</h2>
        <p>${post.content.substring(0, 100)}...</p>
        <time datetime="${post.created_at}">
          ${new Date(post.created_at).toLocaleDateString()}
        </time>
        <a href="../pdf/${post.id}.pdf" class="btn" target="_blank">📄 Download PDF</a>
      `;

      postsContainer.appendChild(article);
    });
  });
});

