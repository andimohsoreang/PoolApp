/**
 * Socket.io Server for Real-time Notifications
 *
 * This server listens for events and broadcasts notifications to connected clients
 */

import http from 'http';
import { Server } from 'socket.io';
import express from 'express';
import bodyParser from 'body-parser';
import cors from 'cors';

// Create HTTP server
const server = http.createServer((req, res) => {
    // Log all incoming requests for debugging
    console.log(`Received request: ${req.method} ${req.url}`);
    res.writeHead(200, { 'Content-Type': 'text/plain' });
    res.end('Socket.io server is running');
});

// Detailed CORS configuration
const corsOptions = {
    origin: "*", // Allow all origins
    methods: ["GET", "POST", "PUT", "DELETE", "OPTIONS"],
    allowedHeaders: ["Origin", "X-Requested-With", "Content-Type", "Accept", "Authorization"],
    credentials: true
};

// Configure Socket.io with CORS
const io = new Server(server, {
    cors: corsOptions,
    allowEIO3: true, // For backward compatibility
    pingTimeout: 60000, // Increase ping timeout to handle slow connections
    transports: ['websocket', 'polling']
});

// Store active connections and subscriptions for debugging
let activeConnections = 0;
const channelSubscriptions = new Map(); // For tracking subscribers by channel

// Listen for connections
io.on('connection', (socket) => {
    console.log(`[${new Date().toISOString()}] New client connected: ${socket.id} from ${socket.handshake.address}`);
    activeConnections++;

    // Log all socket events for debugging
    socket._onevent = function (packet) {
        const args = packet.data || [];
        console.log(`[${new Date().toISOString()}] Socket event received: ${args[0]} - ${JSON.stringify(args.slice(1))}`);
        socket.onevent(packet);
    };

    // Client subscribes to a channel
    socket.on('subscribe', (channel) => {
        console.log(`[${new Date().toISOString()}] Client ${socket.id} subscribed to channel: ${channel}`);
        socket.join(channel);

        // Track subscriptions for debugging
        if (!channelSubscriptions.has(channel)) {
            channelSubscriptions.set(channel, new Set());
        }
        channelSubscriptions.get(channel).add(socket.id);

        // Send confirmation back to client
        socket.emit('subscribed', {
            channel,
            status: 'ok',
            timestamp: new Date().toISOString()
        });

        // Debug info - send current state of the channel
        const roomInfo = io.sockets.adapter.rooms.get(channel);
        console.log(`[${new Date().toISOString()}] Channel ${channel} now has ${roomInfo ? roomInfo.size : 0} clients`);
    });

    // Client unsubscribes from a channel
    socket.on('unsubscribe', (channel) => {
        console.log(`[${new Date().toISOString()}] Client ${socket.id} unsubscribed from channel: ${channel}`);
        socket.leave(channel);

        // Remove from tracking
        if (channelSubscriptions.has(channel)) {
            channelSubscriptions.get(channel).delete(socket.id);
        }
    });

    // Handle disconnections
    socket.on('disconnect', (reason) => {
        console.log(`[${new Date().toISOString()}] Client disconnected: ${socket.id} - Reason: ${reason}`);
        activeConnections--;

        // Clean up subscriptions tracking
        for (const [channel, subscribers] of channelSubscriptions.entries()) {
            if (subscribers.has(socket.id)) {
                subscribers.delete(socket.id);
                console.log(`[${new Date().toISOString()}] Removed ${socket.id} from channel ${channel}`);
            }
        }
    });

    // Handle errors
    socket.on('error', (error) => {
        console.error(`[${new Date().toISOString()}] Socket error for ${socket.id}:`, error);
    });

    // Send initial connection confirmation
    socket.emit('connected', {
        socketId: socket.id,
        message: 'Successfully connected to notification server',
        timestamp: new Date().toISOString()
    });
});

// Create a route to broadcast events (called by Laravel app)
const broadcastEvent = (channel, event, data) => {
    console.log(`[${new Date().toISOString()}] Broadcasting to channel ${channel}, event: ${event}`, data);

    // Check if there are any subscribers in this channel
    const roomInfo = io.sockets.adapter.rooms.get(channel);
    const subscriberCount = roomInfo ? roomInfo.size : 0;
    console.log(`[${new Date().toISOString()}] Channel ${channel} has ${subscriberCount} subscribers`);

    io.to(channel).emit(event, data);
    return {
        success: true,
        message: 'Event broadcasted',
        subscribers: subscriberCount,
        timestamp: new Date().toISOString()
    };
};

// API endpoint to receive events from Laravel
const app = express();

// Enable CORS for Express
app.use(cors(corsOptions));
app.use(bodyParser.json({ limit: '5mb' }));

// Log all incoming API requests
app.use((req, res, next) => {
    console.log(`[${new Date().toISOString()}] API Request: ${req.method} ${req.path} ${JSON.stringify(req.body || {})}`);
    next();
});

// Handle preflight requests
app.options('*', cors(corsOptions));

// Test endpoint
app.get('/ping', (req, res) => {
    res.json({
        pong: true,
        timestamp: new Date().toISOString(),
        connections: activeConnections
    });
});

// Endpoint to broadcast events
app.post('/broadcast', (req, res) => {
    const { channel, event, data } = req.body;

    if (!channel || !event || !data) {
        console.error('[${new Date().toISOString()}] Missing parameters:', req.body);
        return res.status(400).json({
            success: false,
            message: 'Missing required parameters: channel, event, or data',
            timestamp: new Date().toISOString()
        });
    }

    try {
        const result = broadcastEvent(channel, event, data);
        return res.json(result);
    } catch (error) {
        console.error(`[${new Date().toISOString()}] Broadcast error:`, error);
        return res.status(500).json({
            success: false,
            message: 'Error broadcasting event',
            error: error.message,
            timestamp: new Date().toISOString()
        });
    }
});

// Status endpoint with detailed connection info
app.get('/status', (req, res) => {
    // Build channel subscription info
    const channels = {};
    for (const [channel, subscribers] of channelSubscriptions.entries()) {
        channels[channel] = {
            subscribers: [...subscribers],
            count: subscribers.size
        };
    }

    return res.json({
        status: 'online',
        connections: activeConnections,
        channels: channels,
        uptime: process.uptime(),
        timestamp: new Date().toISOString()
    });
});

// Debug endpoint to list all active sockets
app.get('/sockets', (req, res) => {
    const sockets = [];
    for (const [id, socket] of io.sockets.sockets) {
        sockets.push({
            id,
            address: socket.handshake.address,
            transport: socket.conn.transport.name,
            connectedAt: new Date(socket.handshake.issued).toISOString()
        });
    }

    return res.json({
        count: sockets.length,
        sockets: sockets
    });
});

// Endpoint to manually trigger a test notification
app.post('/test-notification', (req, res) => {
    const { channel = 'admin-notifications', event = 'test-event' } = req.body;

    const testData = {
        id: Date.now(),
        type: 'test',
        message: 'This is a test notification from the Socket.IO server',
        created_at: new Date().toISOString()
    };

    try {
        const result = broadcastEvent(channel, event, testData);
        return res.json({
            ...result,
            testData
        });
    } catch (error) {
        return res.status(500).json({
            success: false,
            message: 'Error sending test notification',
            error: error.message
        });
    }
});

// Wildcard route for debugging CORS
app.get('*', (req, res) => {
    res.status(200).json({
        message: 'Socket.io server is running',
        path: req.path,
        method: req.method,
        timestamp: new Date().toISOString()
    });
});

// Create combined server
const PORT = process.env.SOCKET_PORT || 6001;
const expressServer = app.listen(PORT, () => {
    console.log(`[${new Date().toISOString()}] Socket.IO API server running on port ${PORT}`);
});

// Mount Socket.IO server
const SOCKET_PORT = 6002;
server.listen(SOCKET_PORT, () => {
    console.log(`[${new Date().toISOString()}] Socket.IO server listening on port ${SOCKET_PORT}`);
    console.log(`[${new Date().toISOString()}] Visit http://localhost:${PORT}/status to check server status`);
});
