import dotenv from 'dotenv'
import Clawler from './server'

const run = async () => {
  dotenv.config()
  const server = Clawler.create({
    sqliteLocation: 'db.sqlite',
    subscriptionEndpoint:
      maybeStr(process.env.SUBSCRIPTION_ENDPOINT) ??
      'wss://bsky.social',
    subscriptionReconnectDelay:
      maybeInt(process.env.SUBSCRIPTION_RECONNECT_DELAY) ?? 3000,
  })
  await server.start()
}

const maybeStr = (val?: string) => {
  if (!val) return undefined
  return val
}

const maybeInt = (val?: string) => {
  if (!val) return undefined
  const int = parseInt(val, 10)
  if (isNaN(int)) return undefined
  return int
}

run()
