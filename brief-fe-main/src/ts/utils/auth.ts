import { DecodedToken, JWTResponse, LoginPayload, Role } from "../types"
import { jwtDecode } from "jwt-decode"
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL;

export const setAccessToken = (data: JWTResponse): void => {
  if (data && data.accessToken) {
    localStorage.setItem("accessToken", JSON.stringify(data));
  } else {
    console.error("Invalid JWTResponse: missing accessToken.");
  }
};

export const getAccessToken = (): JWTResponse | null => {
  const tokenString = localStorage.getItem("accessToken");
  if (tokenString) {
    try {
      return JSON.parse(tokenString) as JWTResponse;
    } catch (error) {
      console.error("Error parsing accessToken from localStorage:", error);
      return null;
    }
  }
  return null;
};

export const login = async ( {email, password}: LoginPayload): Promise<void> => {
  const endpoint = `${API_BASE_URL}/users/login`;
  try {
    const response = await fetch(endpoint, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      credentials: "include",
      body: JSON.stringify({ email, password }),
    });

    if (!response.ok) {
      throw new Error("Login failed with status: ${response.status}");
    }

    // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
    const data: JWTResponse = await response.json()

    console.log(data);

    if (!data.accessToken) {
      throw new Error("Access token is missing in the response.");
    }

    setAccessToken(data);
  } catch (error) {
    console.error("Login error:", error);
    throw error;
  }
};

export const logout = (): void => {
  localStorage.removeItem("accessToken")
}

export const verifyAccessToken = (): null | DecodedToken => {
  const accessTokenData = getAccessToken();
  if (!accessTokenData || !accessTokenData.accessToken) {
    return null;
  }
  return jwtDecode<DecodedToken>(accessTokenData.accessToken);
}


export const hasRole = (role: Role): boolean => {
  const decodedToken = verifyAccessToken();
  if (!decodedToken || !decodedToken.roles) {
    return false;
  }
  return decodedToken.roles.includes(role);
};

export const refreshAccessToken = async (): Promise<void> => {
  const refreshToken = getAccessToken()?.accessToken;

  if (!refreshToken) {
    throw new Error("No refresh token available.");
  }

  const endpoint = `${API_BASE_URL}/refresh-token`;

  try {
    const response = await fetch(endpoint, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      credentials: "include",
      body: JSON.stringify({ refreshToken }),
    });

    if (!response.ok) {
      throw new Error(`Failed to refresh token. Status: ${response.status}`);
    }

    // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
    const data: JWTResponse = await response.json();

    if (!data.accessToken) {
      throw new Error("Access token missing in the refresh response.");
    }

    setAccessToken(data);
    console.log("Access token refreshed successfully.");
  } catch (error) {
    console.error("Error refreshing access token:", error);
    throw error;
  }
};


