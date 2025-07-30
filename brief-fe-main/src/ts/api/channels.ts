import { Channel, ChannelListResponse, ChannelResponse ,Post} from "../types";
import { myFetch } from "../utils/my-fetch";
import { verifyAccessToken, hasRole } from "../utils/auth.ts";

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL;

/**
 * Haalt alle kanalen op. Beheerders zien alles, anderen enkel hun eigen organisatie.
 */
export function fetchChannels(callback: (channels: Channel[]) => void): void {
  const user = verifyAccessToken();
  // console.log("Roles van ingelogde user:", user.roles);
console.log("Is beheerder:", hasRole("beheerder"));

  if (!user) {
    console.error("Geen geldige toegangstoken gevonden.");
    return;
  }

  myFetch(`${API_BASE_URL}/channels`)
    .then((response) => {
      if (!response.ok) {
        throw new Error(`Netwerk response was niet ok: ${response.status}`);
      }
      return response.json() as Promise<ChannelListResponse>;
    })
    .then((data) => {
      if (!data.channels || !Array.isArray(data.channels)) {
        console.error("Geen geldige kanalen ontvangen");
        return;
      }

      let filteredChannels = data.channels;

      if (!hasRole("beheerder")) {
        // Filter enkel de kanalen van eigen organisatie
        filteredChannels = filteredChannels.filter(
          (channel) => channel.organizations_id === user.organizations_id
        );
      }

      callback(filteredChannels);
    })
    .catch((error) => {
      console.error("Fout bij het ophalen van kanalen:", error);
    });
}

/**
 * Haalt één specifiek kanaal op, en controleert toegang indien geen beheerder.
 */

export function fetchChannelById(
  channelId: string,
  callback: (data: { channel: Channel; posts: Post[] }) => void
): void {
  const user = verifyAccessToken();
  if (!user) {
    console.error("Geen geldige toegangstoken gevonden.");
    return;
  }

  myFetch(`${API_BASE_URL}/channels/${channelId}/posts`)
    .then((response) => {
      if (!response.ok) {
        throw new Error(`Netwerk response was niet ok: ${response.status}`);
      }
      return response.json() as Promise<{ channel: Channel; posts: Post[] }>;
    })
    .then((data) => {
      callback(data);
    })
    .catch((error) => {
      console.error("Fout bij het ophalen van kanaal met posts:", error);
    });
}


/**
 * Maakt een nieuw kanaal aan. Zorgt ervoor dat niet-beheerders alleen binnen hun eigen organisatie kunnen aanmaken.
 */
export function createChannel(
  channel: Channel,
  callback: (channel: Channel) => void
): void {
  const user = verifyAccessToken();

  if (!user) {
    console.error("Geen geldige toegangstoken gevonden.");
    return;
  }

  // ❗ Beveiliging: Forceer organizations_id voor niet-beheerders
  if (!hasRole("beheerder")) {
    if (typeof user.organizations_id === "number") {
      channel.organizations_id = user.organizations_id;
    } else {
      console.error("Gebruiker heeft geen geldige organizations_id.");
      return;
    }
  }

  myFetch(`${API_BASE_URL}/channels`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(channel),
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`Netwerk response was niet ok: ${response.status}`);
      }
      return response.json() as Promise<ChannelResponse>;
    })
    .then((data) => {
      callback(data.channel);
    })
    .catch((error) => {
      console.error("Fout bij het aanmaken van een kanaal:", error);
    });
}
