export type DatabaseSchema = {
  post: Post
  sub_state: SubState
}

export type Post = {
  uri: string
  text: string
  date: number
}

export type SubState = {
  service: string
  cursor: number
}
