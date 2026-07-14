import { apiRequest } from './http';

export interface LoginResponse {
  user: { id: number; name: string };
  token: string;
}

export function login(email: string, password: string): Promise<LoginResponse> {
  return apiRequest<LoginResponse>('/login', {
    method: 'POST',
    body: { email, password, device_name: 'mobile' },
  });
}
