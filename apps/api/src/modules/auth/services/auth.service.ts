import bcrypt from 'bcrypt';
import { Repository } from 'typeorm';
import { User } from '../entities/user.entity';
import { AppDataSource } from '../../../configs/ormconfig';
import { JwtUtil, TokenPair } from '../utils/jwt.util';

export class AuthService {
    private userRepository: Repository<User>;

    constructor() {
        this.userRepository = AppDataSource.getRepository(User);
    }

    async register(email: string, password: string): Promise<User> {
        // Check if user already exists
        const existingUser = await this.userRepository.findOne({ where: { email } });
        if (existingUser) {
            throw new Error('User with this email already exists');
        }

        // Hash password
        const hashedPassword = await bcrypt.hash(password, 10);

        // Create user
        const user = this.userRepository.create({
            email,
            password: hashedPassword,
        });

        return this.userRepository.save(user);
    }

    async login(email: string, password: string): Promise<{ user: Omit<User, 'password' | 'refreshToken'>; tokens: TokenPair }> {
        // Find user by email
        const user = await this.userRepository.findOne({ where: { email } });
        if (!user) {
            throw new Error('Invalid email or password');
        }

        // Verify password
        const isPasswordValid = await bcrypt.compare(password, user.password);
        if (!isPasswordValid) {
            throw new Error('Invalid email or password');
        }

        // Generate tokens
        const tokens = JwtUtil.generateTokenPair({
            userId: user.id,
            email: user.email,
        });

        // Save refresh token to database
        user.refreshToken = tokens.refreshToken;
        await this.userRepository.save(user);

        // Return user without sensitive data
        const { password: _, refreshToken: __, ...userWithoutSensitiveData } = user;

        return {
            user: userWithoutSensitiveData,
            tokens,
        };
    }

    async logout(userId: string): Promise<void> {
        const user = await this.userRepository.findOne({ where: { id: userId } });
        if (user) {
            user.refreshToken = null;
            await this.userRepository.save(user);
        }
    }

    async refreshTokens(refreshToken: string): Promise<TokenPair> {
        // Verify refresh token
        const payload = JwtUtil.verifyRefreshToken(refreshToken);

        // Find user and verify refresh token matches
        const user = await this.userRepository.findOne({ where: { id: payload.userId } });
        if (!user || user.refreshToken !== refreshToken) {
            throw new Error('Invalid refresh token');
        }

        // Generate new token pair
        const tokens = JwtUtil.generateTokenPair({
            userId: user.id,
            email: user.email,
        });

        // Update refresh token in database
        user.refreshToken = tokens.refreshToken;
        await this.userRepository.save(user);

        return tokens;
    }

    async getUserById(userId: string): Promise<User | null> {
        return this.userRepository.findOne({ where: { id: userId } });
    }
}

