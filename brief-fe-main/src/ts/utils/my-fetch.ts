import { getAccessToken, refreshAccessToken, verifyAccessToken, logout} from "./auth.ts";


export const myFetch = async (
  url: RequestInfo | string,
  init: RequestInit = {},
): Promise<Response> => {

    const decodedToken = verifyAccessToken();
    const currentTimeInSeconds = Math.floor(Date.now() / 1000);

    if (!decodedToken || decodedToken.exp - 60 < currentTimeInSeconds) {
      await refreshAccessToken().catch(() => {
        logout();
        window.location.href = "../";
      });
    }

    const accessToken = getAccessToken()?.accessToken;
    if (!accessToken) {
      throw new Error("Access token is unavailable.");
    }

    init.headers = {
      ...init.headers,
      Authorization: `Bearer ${accessToken}`,
    };

    return fetch(url, init);
  }

