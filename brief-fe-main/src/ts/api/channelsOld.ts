import { Channel, ChannelListResponse, ChannelResponse } from '../types';
import { myFetch } from '../utils/my-fetch';

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL;
export function fetchChannels(callback: (channels: Channel[]) => void): void {
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
      callback(data.channels);
    })
    .catch((error) => {
      console.error("Fout bij het ophalen van kanalen:", error);
    });
}

export function fetchChannelById(
  channelId: string,
  callback: (channel: Channel) => void
): void {
  myFetch(`${API_BASE_URL}/channels/${channelId}`)
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
      console.error("Fout bij het ophalen van een kanaal:", error);
    });
}

export function createChannel(
  channel: Channel,
  callback: (channel: Channel) => void
): void {
  myFetch(`${API_BASE_URL}/channels`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
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
