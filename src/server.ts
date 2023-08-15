import { MongoClient, ServerApiVersion} from 'mongodb'
import { Subscription } from './subscription'
import { Config } from './config'

export class Clawler {
  public subscription: Subscription
  public cfg: Config

  constructor(
    subscription: Subscription,
    cfg: Config,
  ) {
    this.subscription = subscription
    this.cfg = cfg
  }

  static create(cfg: Config) {
    const col = new MongoClient(cfg.dbUri, {
      serverApi: {
        version: ServerApiVersion.v1,
        strict: true,
        deprecationErrors: true,
      }
    }).db(cfg.dbName).collection('post');
    const subscription = new Subscription(col, cfg.subscriptionEndpoint)

    return new Clawler(subscription, cfg)
  }

  async start(): Promise<void> {
    this.subscription.run(this.cfg.subscriptionReconnectDelay)
    return
  }
}

export default Clawler
