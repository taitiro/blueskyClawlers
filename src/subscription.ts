import {
  OutputSchema as RepoEvent,
  isCommit,
} from './lexicon/types/com/atproto/sync/subscribeRepos'
import { SubscriptionBase, getOpsByType } from './util/subscription'

export class Subscription extends SubscriptionBase {
  async handleEvent(evt: RepoEvent) {
    if (!isCommit(evt)) return
    const ops = await getOpsByType(evt)

    const postsToDelete = ops.posts.deletes.map((del) => del.uri)
    const postsToCreate = ops.posts.creates
      .filter((create) => {
        // only Japanese posts
        if(create.record.langs){
          return create.record.langs.includes('ja')
        }else{
          return false
        }
      })
      .map((create) => {
        return {
          uri: create.uri,
          text: create.record.text,
          date: Math.floor(new Date(create.record.createdAt).getTime() / 1000),
        }
      })
    if (postsToCreate.length > 0) {
      await this.col.insertMany(postsToCreate)
    }
    if (postsToDelete.length > 0) {
      await this.col.deleteMany({
        date: {$in: postsToDelete}
      })
    }
  }
}
