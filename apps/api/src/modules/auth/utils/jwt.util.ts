import jwt, { SignOptions } from 'jsonwebtoken';

const JWT_SECRET = process.env.JWT_SECRET || 'your-secret-key-change-in-production';
const JWT_REFRESH_SECRET = process.env.JWT_REFRESH_SECRET || 'your-refresh-secret-key-change-in-production';
const JWT_ACCESS_EXPIRY = process.env.JWT_ACCESS_EXPIRY || '15m';
const JWT_REFRESH_EXPIRY = process.env.JWT_REFRESH_EXPIRY || '7d';

export interface TokenPayload {
    userId: string;
    email: string;
}

export interface TokenPair {
    accessToken: string;
    refreshToken: string;
}

export class JwtUtil {
    static generateAccessToken(payload: TokenPayload): string {
        const options: SignOptions = {
            expiresIn: JWT_ACCESS_EXPIRY,
        };
        return jwt.sign(payload, JWT_SECRET, options);
    }

    static generateRefreshToken(payload: TokenPayload): string {
        const options: SignOptions = {
            expiresIn: JWT_REFRESH_EXPIRY,
        };
        return jwt.sign(payload, JWT_REFRESH_SECRET, options);
    }

    static generateTokenPair(payload: TokenPayload): TokenPair {
        return {
            accessToken: this.generateAccessToken(payload),
            refreshToken: this.generateRefreshToken(payload),
        };
    }

    static verifyAccessToken(token: string): TokenPayload {
        try {
            return jwt.verify(token, JWT_SECRET) as TokenPayload;
        } catch (error) {
            throw new Error('Invalid or expired access token');
        }
    }

    static verifyRefreshToken(token: string): TokenPayload {
        try {
            return jwt.verify(token, JWT_REFRESH_SECRET) as TokenPayload;
        } catch (error) {
            throw new Error('Invalid or expired refresh token');
        }
    }
}

