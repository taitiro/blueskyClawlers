import { createDb, Database, migrateToLatest } from './db'
import { Subscription } from './subscription'
import { Config } from './config'

export class Clawler {
  public db: Database
  public subscription: Subscription
  public cfg: Config

  constructor(
    db: Database,
    subscription: Subscription,
    cfg: Config,
  ) {
    this.db = db
    this.subscription = subscription
    this.cfg = cfg
  }

  static create(cfg: Config) {
    const db = createDb(cfg.sqliteLocation)
    const subscription = new Subscription(db, cfg.subscriptionEndpoint)

    return new Clawler(db, subscription, cfg)
  }

  async start(): Promise<void> {
    await migrateToLatest(this.db)
    this.subscription.run(this.cfg.subscriptionReconnectDelay)
    return
  }
}

export default Clawler
