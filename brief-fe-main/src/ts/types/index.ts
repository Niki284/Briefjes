export type Role = "abonnee" | "beheerder";
export interface DecodedToken {
  sub: number;
  iat: number;
  exp: number;
  roles: Role[];
  email?: string;
  name?: string;
  approved?: string;
  organizations_id?: number;
}

export interface JWTResponse {
  accessToken: string;
  refresh_token?: string;
  expires_in: number;
  token_type: "Bearer";
}


export interface LoginPayload {
  email: string,
  password: string,
}

export interface RegisterPayload {
  email: string,
  password: string,
  approved: string,
  role: Role,
  organizations_id: number,
}

export interface User {
  id: number;
  name: string;
  email: string;
  approved: string;
  organizations_id: number;
  role: Role;
}


export interface Channel {
  id: string;
  name: string;
  usersId: string[];
  userRol: Role;
  organizations_id: number; // or number, depending on your backend
}

export interface Post {
  id: string;
  title: string;
  content: string;
  pdf_path: string;
  created_at: string;
  userId: string;
  userRol: Role;
  channelId: string;
}

export interface PostResponse {
  post: Post;
}

export interface ChannelResponse {
  channel: Channel;
}

export interface ChannelListResponse {
  channels: Channel[];
}

export interface UserResponse {
  user: User;
}



